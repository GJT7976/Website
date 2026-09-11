<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_editor_can_view_orders(): void
    {
        $editor = User::factory()->contentEditor()->create();
        $order = Order::factory()->create();

        $this->actingAs($editor)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.orders.show', $order))->assertOk();
    }

    public function test_content_editor_cannot_issue_a_refund(): void
    {
        $editor = User::factory()->contentEditor()->create();
        $order = Order::factory()->create(['payment_status' => 'paid', 'stripe_payment_intent_id' => 'pi_123']);

        $this->actingAs($editor)->post(route('admin.orders.refund', $order))->assertForbidden();
    }

    public function test_order_without_a_payment_intent_cannot_be_refunded(): void
    {
        $owner = User::factory()->owner()->create();
        $order = Order::factory()->create(['stripe_payment_intent_id' => null]);

        $this->actingAs($owner)
            ->post(route('admin.orders.refund', $order))
            ->assertSessionHasErrors('refund');
    }

    public function test_guest_cannot_view_orders(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
    }
}
