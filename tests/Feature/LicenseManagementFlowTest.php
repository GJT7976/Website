<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\LicenseVerificationCode;
use App\Services\LicenseKeyGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * §10's self-service device-management web flow end to end: request a
 * code, verify it, view devices, deactivate one — the same no-account,
 * signed-URL pattern My Downloads uses.
 */
class LicenseManagementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_verify_and_manage_devices(): void
    {
        Mail::fake();

        $app = App::factory()->create();
        $key = app(LicenseKeyGenerator::class)->generate();
        $license = License::create([
            'app_id' => $app->id, 'license_key_hash' => $key['hash'], 'license_key_encrypted' => $key['raw'],
            'customer_name' => 'Jane Doe', 'customer_email' => 'jane@example.com',
            'platform_entitlement' => 'android_only', 'price_paid_cents' => 299, 'currency' => 'USD',
            'payment_provider' => 'stripe', 'purchase_date' => now(), 'maximum_devices' => 2, 'status' => 'active',
        ]);
        $device = LicenseDevice::create([
            'license_id' => $license->id, 'platform' => 'android', 'device_identifier_hash' => hash('sha256', 'phone-1'),
            'label' => 'Android device 1', 'status' => 'active', 'activated_at' => now(), 'last_validated_at' => now(),
        ]);

        $this->post(route('license.manage.send-code'), ['email' => 'jane@example.com', 'license_key' => $key['raw']])
            ->assertRedirect(route('license.manage.verify-form', ['email' => 'jane@example.com']));

        $code = LicenseVerificationCode::where('license_id', $license->id)->first();
        $this->assertNotNull($code);

        // The raw code isn't stored — recover it the same way the test can:
        // by trying every 6-digit code is impractical, so instead assert
        // the hash matches what verifyManagementCode would check against a
        // known code by round-tripping through the service directly.
        $rawCode = '123456';
        $code->update(['code_hash' => hash('sha256', $rawCode)]);

        $verify = $this->post(route('license.manage.verify'), ['email' => 'jane@example.com', 'code' => $rawCode]);
        $verify->assertRedirect();
        $manageUrl = $verify->headers->get('Location');

        $show = $this->get($manageUrl);
        $show->assertOk()->assertSee('Android device 1');

        $deactivateUrl = null;
        preg_match('/action="([^"]+deactivate[^"]*)"/', $show->getContent(), $matches);
        $deactivateUrl = $matches[1] ?? null;
        $this->assertNotNull($deactivateUrl, 'expected a deactivate form action URL on the management page');

        $this->post(html_entity_decode($deactivateUrl))->assertRedirect();

        $this->assertSame('deactivated', $device->refresh()->status);
    }
}
