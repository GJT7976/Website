<?php

namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\CustomerEntitlement;
use App\Models\Order;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EntitlementOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_grant_a_platform_entitlement(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        $this->actingAs($admin)->post(route('admin.apps.entitlements.store', $app), [
            'email' => 'comp@example.com',
            'platform_id' => $android->id,
            'access_type' => 'download',
        ])->assertRedirect();

        $this->assertDatabaseHas('customer_entitlements', [
            'app_id' => $app->id,
            'customer_email' => 'comp@example.com',
            'source' => 'admin_grant',
            'granted_by' => $admin->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_revoke_and_restore_an_entitlement(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);
        $entitlement = CustomerEntitlement::create([
            'app_id' => $app->id, 'platform_id' => $android->id, 'access_type' => 'download',
            'customer_email' => 'jane@example.com', 'status' => 'active', 'source' => 'purchase',
        ]);

        $this->actingAs($admin)->post(route('admin.entitlements.revoke', $entitlement), ['reason' => 'chargeback'])->assertRedirect();
        $entitlement->refresh();
        $this->assertSame('revoked', $entitlement->status);
        $this->assertSame('chargeback', $entitlement->revoked_reason);
        $this->assertSame($admin->id, $entitlement->revoked_by);

        $this->actingAs($admin)->post(route('admin.entitlements.restore', $entitlement))->assertRedirect();
        $this->assertSame('active', $entitlement->refresh()->status);
        $this->assertNull($entitlement->revoked_reason);
    }

    public function test_admin_can_resend_the_order_receipt(): void
    {
        Mail::fake();
        $admin = User::factory()->owner()->create();
        $order = Order::factory()->create(['payment_status' => 'paid']);

        $this->actingAs($admin)->post(route('admin.orders.resend-receipt', $order))->assertRedirect();

        Mail::assertSent(\App\Mail\OrderReceipt::class);
    }
}
