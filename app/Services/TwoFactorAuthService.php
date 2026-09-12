<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Thin wrapper around pragmarx/google2fa (TOTP/RFC 6238) + bacon/bacon-qr-code
 * — the same "thin wrapper around a third-party lib" shape as
 * StripeCheckout. The QR code is rendered as inline SVG on the server so
 * the shared secret is never sent to a third-party QR image API.
 */
class TwoFactorAuthService
{
    public function __construct(private Google2FA $google2fa) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $qrUrl = $this->google2fa->getQRCodeUrl('Niagara Inde Apps', $user->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($qrUrl);
    }

    /**
     * ±1 step (~30s) of clock drift tolerance either side.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->google2fa->verifyKey($user->two_factor_secret, $code, 1) === true;
    }

    /**
     * @return string[]
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => strtoupper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    /**
     * Recovery codes are stored bcrypt-hashed inside the (already
     * encrypted-at-rest) two_factor_recovery_codes column. A code is
     * removed from the array the moment it's used — one-time-use.
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $hashed) {
            if (Hash::check($code, $hashed)) {
                unset($codes[$index]);
                // forceFill, not update(): two_factor_recovery_codes is
                // deliberately excluded from #[Fillable] (see the users
                // migration's docblock), so a plain update() would
                // silently drop it.
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
