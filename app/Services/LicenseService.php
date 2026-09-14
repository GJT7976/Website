<?php

namespace App\Services;

use App\Mail\LicenseIssuedResend;
use App\Mail\LicenseManagementCode;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\LicenseEvent;
use App\Models\LicenseVerificationCode;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * The only place License / LicenseDevice rows are created or changed —
 * mirrors App\Services\EntitlementService's role for customer_entitlements.
 * Implements §7 (platform entitlements), §8/§9 (device activation and the
 * two-device rule), §11 (self-service reset abuse limiting), and §14
 * (signed offline entitlement tokens).
 */
class LicenseService
{
    public function __construct(
        private LicenseKeyGenerator $keys,
        private LicenseTokenSigner $signer,
    ) {}

    /**
     * Called once an order is confirmed paid (StripeWebhookHandler only),
     * right alongside EntitlementService::createFromOrder(). One License
     * per order item whose edition grants Android and/or Windows
     * *download* access — a Web/PWA-only edition gets no license.
     * firstOrCreate() on order_item_id keeps this replay-safe (§5).
     *
     * @return Collection<int, License>
     */
    public function createFromOrder(Order $order): Collection
    {
        $created = new Collection;

        foreach ($order->items as $item) {
            if (! $item->app_edition_id) {
                continue;
            }

            $edition = $item->edition()->with('entitlements.platform')->first();

            if (! $edition) {
                continue;
            }

            $downloadPlatforms = $edition->entitlements
                ->where('access_type', 'download')
                ->pluck('platform.code')
                ->filter(fn ($code) => in_array($code, ['android', 'windows'], true))
                ->unique()
                ->values();

            if ($downloadPlatforms->isEmpty()) {
                continue;
            }

            $entitlement = match (true) {
                $downloadPlatforms->count() === 2 => 'android_windows_bundle',
                $downloadPlatforms->first() === 'android' => 'android_only',
                default => 'windows_only',
            };

            $existing = License::where('order_item_id', $item->id)->first();

            if ($existing) {
                $created->push($existing);

                continue;
            }

            $key = $this->keys->generate();

            $license = License::create([
                'app_id' => $item->app_id,
                'app_edition_id' => $edition->id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'license_key_hash' => $key['hash'],
                'license_key_encrypted' => $key['raw'],
                'customer_name' => $order->customer_name,
                'customer_email' => mb_strtolower(trim($order->customer_email)),
                'platform_entitlement' => $entitlement,
                'price_paid_cents' => $item->line_subtotal_cents,
                'currency' => $order->currency,
                'transaction_id' => $order->stripe_payment_intent_id,
                'payment_provider' => $order->payment_provider ?? 'stripe',
                'purchase_date' => $order->created_at,
                'maximum_devices' => config('licensing.default_maximum_devices'),
                'status' => 'active',
            ]);

            $created->push($license);
        }

        return $created;
    }

    public function resolveByRawKey(string $rawKey): ?License
    {
        return License::where('license_key_hash', LicenseKeyGenerator::hash($rawKey))->first();
    }

    /**
     * §8/§9: validate + register (or re-touch) a device activation.
     *
     * @return array{unlocked: bool, reason: ?string, message: ?string, token: ?array}
     */
    public function activate(License $license, App $app, string $platform, string $deviceIdentifier, ?string $appVersion, ?string $ip): array
    {
        if ($license->app_id !== $app->id) {
            $this->logEvent($license, 'rejected_product', null, $platform, $ip);

            return $this->locked('wrong_product', 'This license key is not valid for this app.');
        }

        if ($license->status !== 'active') {
            $this->logEvent($license, 'rejected_status', null, $platform, $ip);

            return $this->locked($license->status, $this->statusMessage($license->status));
        }

        if (! in_array($platform, ['android', 'windows'], true) || ! $license->permitsPlatform($platform)) {
            $this->logEvent($license, 'rejected_platform', null, $platform, $ip);

            return $this->locked('platform_not_licensed', 'This license does not cover this platform.');
        }

        $hash = $this->hashDeviceIdentifier($deviceIdentifier);
        $device = $license->devices()->where('device_identifier_hash', $hash)->first();

        if ($device && $device->status === 'active') {
            $device->update(['last_validated_at' => now(), 'app_version' => $appVersion ?? $device->app_version]);
            $this->logEvent($license, 'validated', $device, $platform, $ip);

            return $this->unlocked($license, $device);
        }

        $activeCount = $license->activeDevices()->count();

        if ($activeCount >= $license->maximum_devices) {
            $this->logEvent($license, 'rejected_max_devices', null, $platform, $ip);

            return $this->locked('max_devices', 'Your license is already activated on the maximum of '.$license->maximum_devices.' devices.');
        }

        $label = ucfirst($platform).' device '.($activeCount + 1);

        if ($device) {
            // A previously deactivated device reactivating (§10 replacement flow).
            $device->update([
                'status' => 'active', 'activated_at' => now(), 'last_validated_at' => now(),
                'deactivated_at' => null, 'deactivated_reason' => null, 'app_version' => $appVersion,
            ]);
        } else {
            $device = LicenseDevice::create([
                'license_id' => $license->id, 'platform' => $platform,
                'device_identifier_hash' => $hash, 'label' => $label, 'app_version' => $appVersion,
                'status' => 'active', 'activated_at' => now(), 'last_validated_at' => now(),
            ]);
        }

        $this->logEvent($license, 'activated', $device, $platform, $ip);

        return $this->unlocked($license, $device);
    }

    /**
     * §13/§14: periodic re-validation of an already-activated device.
     *
     * @return array{unlocked: bool, reason: ?string, message: ?string, token: ?array}
     */
    public function validate(License $license, string $deviceIdentifier, ?string $ip): array
    {
        if ($license->status !== 'active') {
            return $this->locked($license->status, $this->statusMessage($license->status));
        }

        $device = $license->devices()->where('device_identifier_hash', $this->hashDeviceIdentifier($deviceIdentifier))->active()->first();

        if (! $device) {
            return $this->locked('device_not_registered', 'This device is not activated on this license.');
        }

        $device->update(['last_validated_at' => now()]);
        $this->logEvent($license, 'validated', $device, $device->platform, $ip);

        return $this->unlocked($license, $device);
    }

    /**
     * Customer self-service deactivation (§10), subject to the reset-abuse
     * limit (§11). Admin deactivation goes through deactivateByAdmin()
     * instead, which is never rate-limited.
     *
     * @return array{ok: bool, message: ?string}
     */
    public function deactivateByCustomer(LicenseDevice $device, ?string $ip): array
    {
        $license = $device->license;

        $windowStart = Carbon::now()->subDays(config('licensing.self_service_reset_window_days'));
        $recentResets = LicenseEvent::where('license_id', $license->id)
            ->where('type', 'deactivated')
            ->where('created_at', '>=', $windowStart)
            ->count();

        if ($recentResets >= config('licensing.self_service_reset_limit')) {
            $this->logEvent($license, 'reset_rate_limited', $device, $device->platform, $ip);

            return ['ok' => false, 'message' => 'You\'ve reached the limit of self-service device changes for now. Contact support to move your license to a new device.'];
        }

        $device->update(['status' => 'deactivated', 'deactivated_at' => now(), 'deactivated_reason' => 'customer_self']);
        $this->logEvent($license, 'deactivated', $device, $device->platform, $ip);

        return ['ok' => true, 'message' => null];
    }

    public function deactivateByAdmin(LicenseDevice $device, ?string $reason): void
    {
        $device->update(['status' => 'deactivated', 'deactivated_at' => now(), 'deactivated_reason' => $reason ?? 'admin_override']);
        $this->logEvent($device->license, 'deactivated', $device, $device->platform, null);
    }

    public function resetActivations(License $license): void
    {
        $license->activeDevices->each(fn (LicenseDevice $device) => $this->deactivateByAdmin($device, 'admin_reset'));
    }

    public function revoke(License $license): void
    {
        $license->update(['status' => 'revoked']);
    }

    public function restore(License $license): void
    {
        $license->update(['status' => 'active']);
    }

    public function markRefunded(License $license): void
    {
        $license->update(['status' => 'refunded']);
    }

    /**
     * Called from StripeWebhookHandler on a full refund or a dispute
     * (chargeback) — every license tied to the order moves to the given
     * status, same as EntitlementService::revokeForOrder does for
     * customer_entitlements.
     */
    public function revokeForOrder(Order $order, string $status): void
    {
        License::where('order_id', $order->id)->active()->get()
            ->each(fn (License $license) => $license->update(['status' => $status]));
    }

    /**
     * §10: emails a one-time code to the license's own customer_email —
     * never trusting the email address the requester typed until it's
     * matched against the license.
     */
    public function requestManagementCode(string $email, string $rawKey): void
    {
        $license = $this->resolveByRawKey($rawKey);

        if (! $license || mb_strtolower(trim($email)) !== $license->customer_email) {
            return; // deliberately silent — don't reveal whether the key/email matched
        }

        $code = (string) random_int(100000, 999999);

        LicenseVerificationCode::create([
            'license_id' => $license->id,
            'email' => $license->customer_email,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(config('licensing.management_code_ttl_minutes')),
        ]);

        Mail::to($license->customer_email)->send(new LicenseManagementCode($license, $code));
    }

    /**
     * @return string|null a signed management URL, or null if the code was invalid/expired
     */
    public function verifyManagementCode(string $email, string $code): ?string
    {
        $email = mb_strtolower(trim($email));

        $record = LicenseVerificationCode::where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (! $record) {
            return null;
        }

        $record->increment('attempts');

        if ($record->attempts > 5 || $record->code_hash !== hash('sha256', $code)) {
            return null;
        }

        $record->update(['consumed_at' => now()]);

        return URL::temporarySignedRoute(
            'license.manage.show',
            now()->addMinutes(30),
            ['license' => $record->license_id]
        );
    }

    public function resendEmail(License $license): void
    {
        Mail::to($license->customer_email)->send(new LicenseIssuedResend($license));
    }

    private function unlocked(License $license, LicenseDevice $device): array
    {
        $payload = [
            'license_id' => $license->id,
            'app_id' => $license->app_id,
            'device_id' => $device->id,
            'platform' => $device->platform,
            'platform_entitlement' => $license->platform_entitlement,
            'issued_at' => now()->toIso8601String(),
            'valid_until' => now()->addDays(30)->toIso8601String(), // offline grace period (§14)
        ];

        return ['unlocked' => true, 'reason' => null, 'message' => null, 'token' => $this->signer->sign($payload)];
    }

    private function locked(string $reason, string $message): array
    {
        return ['unlocked' => false, 'reason' => $reason, 'message' => $message, 'token' => null];
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'revoked' => 'This license has been revoked.',
            'refunded' => 'This license was refunded and is no longer active.',
            'chargeback' => 'This license was disabled due to a payment chargeback.',
            'disabled' => 'This license has been disabled.',
            default => 'This license is not active.',
        };
    }

    private function hashDeviceIdentifier(string $deviceIdentifier): string
    {
        return hash('sha256', trim($deviceIdentifier));
    }

    private function logEvent(License $license, string $type, ?LicenseDevice $device, ?string $platform, ?string $ip): void
    {
        LicenseEvent::create([
            'license_id' => $license->id,
            'license_device_id' => $device?->id,
            'type' => $type,
            'platform' => in_array($platform, ['android', 'windows'], true) ? $platform : null,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
