<?php

namespace Tests\Feature\Api;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\EditionEntitlement;
use App\Models\License;
use App\Models\Order;
use App\Models\Platform;
use App\Services\LicenseKeyGenerator;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HTTP-level coverage of §26's license API — request validation, JSON
 * response shape, and that the underlying LicenseService rules (already
 * unit-tested in LicenseServiceTest) are wired up correctly end to end.
 */
class LicenseActivationTest extends TestCase
{
    use RefreshDatabase;

    private function issuedAndroidLicense(): array
    {
        $app = App::factory()->create();
        $edition = AppEdition::create([
            'app_id' => $app->id, 'name' => 'Android', 'slug' => 'android-'.uniqid(),
            'price_cents' => 299, 'currency' => 'USD', 'active' => true, 'featured' => false, 'sort_order' => 0,
        ]);
        $android = Platform::firstOrCreate(['code' => 'android'], ['name' => 'Android', 'sort_order' => 0]);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);

        $order = Order::factory()->create(['payment_status' => 'paid']);
        $item = $order->items()->create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name, 'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(), 'license_label_snapshot' => 'PRO',
            'unit_price_cents' => 299, 'quantity' => 1, 'line_subtotal_cents' => 299,
        ]);

        // Bypass LicenseService::createFromOrder() so the test controls
        // the raw key directly, exactly like it would be typed by a customer.
        $key = app(LicenseKeyGenerator::class)->generate();
        $license = License::create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id, 'order_id' => $order->id, 'order_item_id' => $item->id,
            'license_key_hash' => $key['hash'], 'license_key_encrypted' => $key['raw'],
            'customer_name' => $order->customer_name, 'customer_email' => $order->customer_email,
            'platform_entitlement' => 'android_only', 'price_paid_cents' => 299, 'currency' => 'USD',
            'payment_provider' => 'stripe', 'purchase_date' => now(), 'maximum_devices' => 2, 'status' => 'active',
        ]);

        return ['app' => $app, 'license' => $license, 'raw_key' => $key['raw']];
    }

    public function test_activate_unlocks_pro_for_a_valid_key_and_platform(): void
    {
        ['app' => $app, 'raw_key' => $rawKey] = $this->issuedAndroidLicense();

        $response = $this->postJson('/api/license/activate', [
            'license_key' => $rawKey, 'app_id' => $app->id, 'platform' => 'android',
            'device_id' => 'test-device-identifier-1', 'app_version' => '1.0.0',
        ]);

        $response->assertOk()->assertJson(['unlocked' => true]);
        $this->assertNotNull($response->json('token.signature'));
        $this->assertDatabaseHas('license_devices', ['device_identifier_hash' => hash('sha256', 'test-device-identifier-1')]);
    }

    public function test_activate_rejects_an_unlicensed_platform(): void
    {
        ['app' => $app, 'raw_key' => $rawKey] = $this->issuedAndroidLicense();

        $response = $this->postJson('/api/license/activate', [
            'license_key' => $rawKey, 'app_id' => $app->id, 'platform' => 'windows',
            'device_id' => 'test-device-identifier-2',
        ]);

        $response->assertOk()->assertJson(['unlocked' => false, 'reason' => 'platform_not_licensed']);
    }

    public function test_activate_rejects_an_invalid_license_key(): void
    {
        $app = App::factory()->create();

        $response = $this->postJson('/api/license/activate', [
            'license_key' => 'ZZZZ-ZZZZ-ZZZZ-ZZZZ', 'app_id' => $app->id, 'platform' => 'android',
            'device_id' => 'test-device-identifier-3',
        ]);

        $response->assertOk()->assertJson(['unlocked' => false, 'reason' => 'invalid_license']);
    }

    public function test_activate_validates_the_request(): void
    {
        $this->postJson('/api/license/activate', [])->assertStatus(422);
        $this->postJson('/api/license/activate', ['license_key' => 'X', 'app_id' => 1, 'platform' => 'ios', 'device_id' => 'short'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['platform', 'device_id']);
    }

    public function test_validate_endpoint_confirms_an_activated_device(): void
    {
        ['app' => $app, 'raw_key' => $rawKey] = $this->issuedAndroidLicense();

        $this->postJson('/api/license/activate', [
            'license_key' => $rawKey, 'app_id' => $app->id, 'platform' => 'android', 'device_id' => 'device-x',
        ])->assertOk();

        $response = $this->postJson('/api/license/validate', ['license_key' => $rawKey, 'device_id' => 'device-x']);

        $response->assertOk()->assertJson(['unlocked' => true]);
    }

    public function test_deactivate_endpoint_frees_the_device(): void
    {
        ['app' => $app, 'raw_key' => $rawKey] = $this->issuedAndroidLicense();

        $this->postJson('/api/license/activate', [
            'license_key' => $rawKey, 'app_id' => $app->id, 'platform' => 'android', 'device_id' => 'device-y',
        ])->assertOk();

        $response = $this->postJson('/api/license/deactivate', ['license_key' => $rawKey, 'device_id' => 'device-y']);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('license_devices', ['device_identifier_hash' => hash('sha256', 'device-y'), 'status' => 'deactivated']);
    }
}
