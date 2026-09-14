<?php

namespace Tests\Feature\Admin;

use App\Mail\LicenseIssuedResend;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\User;
use App\Services\LicenseKeyGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * §23's admin dashboard actions — revoke, restore, mark refunded, resend
 * email, deactivate a device, reset activations. Mirrors
 * EntitlementOverrideTest's style for the equivalent entitlement actions.
 */
class LicenseAdminTest extends TestCase
{
    use RefreshDatabase;

    private function license(): License
    {
        $app = App::factory()->create();
        $key = app(LicenseKeyGenerator::class)->generate();

        return License::create([
            'app_id' => $app->id, 'license_key_hash' => $key['hash'], 'license_key_encrypted' => $key['raw'],
            'customer_name' => 'Jane Doe', 'customer_email' => 'jane@example.com',
            'platform_entitlement' => 'android_only', 'price_paid_cents' => 299, 'currency' => 'USD',
            'payment_provider' => 'stripe', 'purchase_date' => now(), 'maximum_devices' => 2, 'status' => 'active',
        ]);
    }

    public function test_admin_can_revoke_and_restore_a_license(): void
    {
        $admin = User::factory()->owner()->create();
        $license = $this->license();

        $this->actingAs($admin)->post(route('admin.licenses.revoke', $license))->assertRedirect();
        $this->assertSame('revoked', $license->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'license.revoked']);

        $this->actingAs($admin)->post(route('admin.licenses.restore', $license))->assertRedirect();
        $this->assertSame('active', $license->refresh()->status);
    }

    public function test_admin_can_mark_a_license_refunded(): void
    {
        $admin = User::factory()->owner()->create();
        $license = $this->license();

        $this->actingAs($admin)->post(route('admin.licenses.mark-refunded', $license))->assertRedirect();

        $this->assertSame('refunded', $license->refresh()->status);
    }

    public function test_admin_can_deactivate_a_device_and_reset_activations(): void
    {
        $admin = User::factory()->owner()->create();
        $license = $this->license();
        $device = LicenseDevice::create([
            'license_id' => $license->id, 'platform' => 'android', 'device_identifier_hash' => hash('sha256', 'phone'),
            'label' => 'Android device 1', 'status' => 'active', 'activated_at' => now(), 'last_validated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.license-devices.deactivate', $device))->assertRedirect();
        $this->assertSame('deactivated', $device->refresh()->status);

        $device2 = LicenseDevice::create([
            'license_id' => $license->id, 'platform' => 'android', 'device_identifier_hash' => hash('sha256', 'tablet'),
            'label' => 'Android device 2', 'status' => 'active', 'activated_at' => now(), 'last_validated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.licenses.reset-activations', $license))->assertRedirect();
        $this->assertSame('deactivated', $device2->refresh()->status);
    }

    public function test_admin_can_resend_the_license_email(): void
    {
        Mail::fake();
        $admin = User::factory()->owner()->create();
        $license = $this->license();

        $this->actingAs($admin)->post(route('admin.licenses.resend-email', $license))->assertRedirect();

        Mail::assertSent(LicenseIssuedResend::class);
    }

    public function test_admin_can_search_licenses_by_email(): void
    {
        $admin = User::factory()->owner()->create();
        $this->license();

        $this->actingAs($admin)->get(route('admin.licenses.index', ['q' => 'jane@example.com']))
            ->assertOk()
            ->assertSee('jane@example.com');
    }
}
