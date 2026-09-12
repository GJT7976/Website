<?php

namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Services\StripeCheckout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Refund;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_audit_log(): void
    {
        $owner = User::factory()->owner()->create();
        AuditLog::factory()->count(3)->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('admin.audit-log.index'))->assertOk();
    }

    public function test_content_editor_cannot_view_audit_log(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.audit-log.index'))->assertForbidden();
    }

    public function test_creating_an_app_writes_an_audit_log_entry(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('admin.apps.store'), [
            'name' => 'Test App',
            'slug' => 'test-app',
            'tagline' => 'A tagline',
            'short_description' => 'Short description',
            'status' => 'draft',
            'license_type' => 'personal',
            'update_policy' => 'updates_included',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'app.created',
            'user_id' => $owner->id,
        ]);
    }

    public function test_updating_app_price_records_before_and_after(): void
    {
        $owner = User::factory()->owner()->create();
        $app = App::factory()->create(['name' => 'Original Name', 'price_cents' => 500, 'currency' => 'USD', 'is_free' => false]);

        $this->actingAs($owner)->put(route('admin.apps.update', $app), [
            'name' => 'Updated Name',
            'slug' => $app->slug,
            'tagline' => $app->tagline,
            'short_description' => $app->short_description,
            'status' => $app->status,
            'license_type' => 'personal',
            'update_policy' => 'updates_included',
            'price' => '9.99',
            'currency' => 'USD',
        ]);

        $log = AuditLog::where('action', 'app.updated')->first();

        $this->assertNotNull($log);
        $this->assertSame('Original Name', $log->before['name']);
        $this->assertSame('Updated Name', $log->after['name']);
    }

    public function test_refunding_an_order_writes_an_audit_log_entry(): void
    {
        $owner = User::factory()->owner()->create();
        $order = Order::factory()->create([
            'payment_status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_123',
            'total_cents' => 1000,
        ]);

        $this->mock(StripeCheckout::class, function ($mock) {
            $mock->shouldReceive('refund')->once()->andReturn(Refund::constructFrom(['id' => 're_test_123']));
        });

        $this->actingAs($owner)->post(route('admin.orders.refund', $order), []);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.refunded',
            'user_id' => $owner->id,
        ]);
    }

    public function test_no_delete_route_exists_for_audit_logs(): void
    {
        $this->expectException(RouteNotFoundException::class);

        route('admin.audit-log.destroy', 1);
    }
}
