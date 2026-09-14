<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\EditionEntitlement;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers §33's backend-testable cases for the license-key/device-activation
 * system: issuance from a paid order, the two-device rule, platform
 * entitlement enforcement, and status transitions. Mirrors
 * StripeWebhookHandlerTest's style (fake \Stripe\Event, no network) for
 * the webhook-integration cases and exercises LicenseService directly for
 * everything else, matching how EntitlementService is tested.
 */
class LicenseServiceTest extends TestCase
{
    use RefreshDatabase;

    private function platform(string $code): Platform
    {
        return Platform::firstOrCreate(['code' => $code], ['name' => ucfirst($code), 'sort_order' => 0]);
    }

    /**
     * @param  array<string>  $platforms  e.g. ['android'], ['windows'], ['android', 'windows']
     */
    private function orderItemFor(array $platforms, int $priceCents = 299): OrderItem
    {
        $app = App::factory()->create();

        $edition = AppEdition::create([
            'app_id' => $app->id, 'name' => implode('+', $platforms), 'slug' => implode('-', $platforms).'-'.uniqid(),
            'price_cents' => $priceCents, 'currency' => 'USD', 'active' => true, 'featured' => false, 'sort_order' => 0,
        ]);

        foreach ($platforms as $code) {
            EditionEntitlement::create([
                'app_edition_id' => $edition->id,
                'platform_id' => $this->platform($code)->id,
                'access_type' => 'download',
            ]);
        }

        $order = Order::factory()->create(['payment_status' => 'paid', 'currency' => 'USD']);

        return $order->items()->create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name, 'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(), 'license_label_snapshot' => 'PRO',
            'unit_price_cents' => $priceCents, 'quantity' => 1, 'line_subtotal_cents' => $priceCents,
        ]);
    }

    public function test_android_only_purchase_issues_an_android_only_license(): void
    {
        $item = $this->orderItemFor(['android']);

        $licenses = app(LicenseService::class)->createFromOrder($item->order);

        $this->assertCount(1, $licenses);
        $this->assertSame('android_only', $licenses->first()->platform_entitlement);
        $this->assertSame(2, $licenses->first()->maximum_devices);
    }

    public function test_windows_only_purchase_issues_a_windows_only_license(): void
    {
        $item = $this->orderItemFor(['windows']);

        $license = app(LicenseService::class)->createFromOrder($item->order)->first();

        $this->assertSame('windows_only', $license->platform_entitlement);
    }

    public function test_bundle_purchase_issues_one_license_covering_both_platforms(): void
    {
        $item = $this->orderItemFor(['android', 'windows'], 499);

        $licenses = app(LicenseService::class)->createFromOrder($item->order);

        $this->assertCount(1, $licenses);
        $this->assertSame('android_windows_bundle', $licenses->first()->platform_entitlement);
    }

    public function test_web_only_edition_gets_no_license(): void
    {
        $app = App::factory()->create();
        $edition = AppEdition::create([
            'app_id' => $app->id, 'name' => 'Web', 'slug' => 'web-'.uniqid(),
            'price_cents' => 199, 'currency' => 'USD', 'active' => true, 'featured' => false, 'sort_order' => 0,
        ]);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $this->platform('web')->id, 'access_type' => 'web_access']);

        $order = Order::factory()->create(['payment_status' => 'paid']);
        $order->items()->create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name, 'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(), 'license_label_snapshot' => 'PRO',
            'unit_price_cents' => 199, 'quantity' => 1, 'line_subtotal_cents' => 199,
        ]);

        $licenses = app(LicenseService::class)->createFromOrder($order);

        $this->assertCount(0, $licenses);
    }

    public function test_first_and_second_device_activate_and_third_is_rejected(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();
        $app = $item->app;

        $first = $service->activate($license, $app, 'android', 'device-one', '1.0', '127.0.0.1');
        $this->assertTrue($first['unlocked']);
        $this->assertNotNull($first['token']);

        $second = $service->activate($license, $app, 'android', 'device-two', '1.0', '127.0.0.1');
        $this->assertTrue($second['unlocked']);

        $third = $service->activate($license, $app, 'android', 'device-three', '1.0', '127.0.0.1');
        $this->assertFalse($third['unlocked']);
        $this->assertSame('max_devices', $third['reason']);
        $this->assertDatabaseHas('license_events', ['license_id' => $license->id, 'type' => 'rejected_max_devices']);
    }

    public function test_reactivating_the_same_device_does_not_consume_another_slot(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();
        $app = $item->app;

        $service->activate($license, $app, 'android', 'same-device', '1.0', null);
        $service->activate($license, $app, 'android', 'same-device', '1.1', null);
        $service->activate($license, $app, 'android', 'same-device', '1.2', null);

        $this->assertSame(1, $license->activeDevices()->count());
    }

    public function test_platform_not_covered_by_the_license_is_rejected(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();

        $result = $service->activate($license, $item->app, 'windows', 'a-windows-pc', null, null);

        $this->assertFalse($result['unlocked']);
        $this->assertSame('platform_not_licensed', $result['reason']);
    }

    public function test_wrong_app_id_is_rejected(): void
    {
        $item = $this->orderItemFor(['android']);
        $otherApp = App::factory()->create();
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();

        $result = $service->activate($license, $otherApp, 'android', 'device-x', null, null);

        $this->assertFalse($result['unlocked']);
        $this->assertSame('wrong_product', $result['reason']);
    }

    public function test_invalid_license_key_resolves_to_null(): void
    {
        $this->assertNull(app(LicenseService::class)->resolveByRawKey('NOPE-NOPE-NOPE-NOPE'));
    }

    public function test_revoked_refunded_and_chargeback_licenses_reject_activation(): void
    {
        $service = app(LicenseService::class);

        foreach (['revoked', 'refunded', 'chargeback', 'disabled'] as $status) {
            $item = $this->orderItemFor(['android']);
            $license = $service->createFromOrder($item->order)->first();
            $license->update(['status' => $status]);

            $result = $service->activate($license, $item->app, 'android', 'device-'.$status, null, null);

            $this->assertFalse($result['unlocked'], "expected {$status} license to reject activation");
            $this->assertSame($status, $result['reason']);
        }
    }

    public function test_device_deactivation_frees_a_slot_for_a_replacement(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();
        $app = $item->app;

        $service->activate($license, $app, 'android', 'old-phone', null, null);
        $service->activate($license, $app, 'android', 'second-device', null, null);

        $oldDevice = $license->devices()->where('device_identifier_hash', hash('sha256', 'old-phone'))->first();
        $deactivation = $service->deactivateByCustomer($oldDevice, '127.0.0.1');
        $this->assertTrue($deactivation['ok']);

        $replacement = $service->activate($license, $app, 'android', 'new-phone', null, null);
        $this->assertTrue($replacement['unlocked']);
        $this->assertSame(2, $license->activeDevices()->count());
    }

    public function test_self_service_reset_limit_blocks_further_deactivations(): void
    {
        config(['licensing.self_service_reset_limit' => 1]);

        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();
        $app = $item->app;

        $service->activate($license, $app, 'android', 'device-a', null, null);
        $deviceA = $license->devices()->first();
        $this->assertTrue($service->deactivateByCustomer($deviceA, null)['ok']);

        $service->activate($license, $app, 'android', 'device-b', null, null);
        $deviceB = $license->devices()->where('device_identifier_hash', hash('sha256', 'device-b'))->first();
        $result = $service->deactivateByCustomer($deviceB, null);

        $this->assertFalse($result['ok']);
        $this->assertDatabaseHas('license_events', ['license_id' => $license->id, 'type' => 'reset_rate_limited']);
    }

    public function test_creating_from_order_twice_does_not_issue_a_second_license(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);

        $first = $service->createFromOrder($item->order);
        $second = $service->createFromOrder($item->order);

        $this->assertSame($first->first()->id, $second->first()->id);
        $this->assertSame(1, License::where('order_item_id', $item->id)->count());
    }

    public function test_revoke_for_order_updates_every_license_on_that_order(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();

        $service->revokeForOrder($item->order, 'refunded');

        $this->assertSame('refunded', $license->refresh()->status);
    }

    public function test_validate_reflects_current_device_and_license_state(): void
    {
        $item = $this->orderItemFor(['android']);
        $service = app(LicenseService::class);
        $license = $service->createFromOrder($item->order)->first();
        $app = $item->app;

        $service->activate($license, $app, 'android', 'validated-device', null, null);

        $ok = $service->validate($license, 'validated-device', null);
        $this->assertTrue($ok['unlocked']);

        $unknown = $service->validate($license, 'never-activated-device', null);
        $this->assertFalse($unknown['unlocked']);
        $this->assertSame('device_not_registered', $unknown['reason']);
    }
}
