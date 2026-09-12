<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Services\StripeWebhookHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Tests\TestCase;

class StripeWebhookHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_session_completed_marks_the_order_paid_and_records_a_payment(): void
    {
        Mail::fake();

        $order = Order::factory()->create(['stripe_checkout_session_id' => 'cs_test_123']);

        $event = Event::constructFrom([
            'id' => 'evt_1', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_123',
                'payment_intent' => 'pi_test_123',
                'amount_total' => $order->total_cents,
                'currency' => strtolower($order->currency),
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('completed', $order->order_status);
        $this->assertSame('pi_test_123', $order->stripe_payment_intent_id);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider_payment_id' => 'pi_test_123',
            'status' => 'succeeded',
        ]);
    }

    public function test_unknown_order_is_ignored_without_error(): void
    {
        $event = Event::constructFrom([
            'id' => 'evt_2', 'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_does_not_exist', 'metadata' => []]],
        ]);

        app(StripeWebhookHandler::class)->handle($event); // should not throw

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_failed_marks_the_order_failed(): void
    {
        $order = Order::factory()->create();

        $event = Event::constructFrom([
            'id' => 'evt_3', 'type' => 'payment_intent.payment_failed',
            'data' => ['object' => [
                'id' => 'pi_test_456',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $this->assertSame('failed', $order->refresh()->payment_status);
    }

    public function test_charge_refunded_records_a_refund_and_updates_payment_status(): void
    {
        $order = Order::factory()->create(['stripe_payment_intent_id' => 'pi_test_789', 'payment_status' => 'paid', 'total_cents' => 1000]);
        Payment::factory()->create(['order_id' => $order->id, 'provider_payment_id' => 'pi_test_789', 'amount_cents' => 1000]);

        $event = Event::constructFrom([
            'id' => 'evt_4', 'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_test_1',
                'payment_intent' => 'pi_test_789',
                'amount_refunded' => 1000,
                'refunds' => ['data' => [
                    ['id' => 're_test_1', 'amount' => 1000, 'reason' => 'requested_by_customer'],
                ]],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $order->refresh();
        $this->assertSame('refunded', $order->payment_status);
        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'stripe_refund_id' => 're_test_1',
            'amount_cents' => 1000,
        ]);
    }

    public function test_partial_refund_marks_order_partially_refunded(): void
    {
        $order = Order::factory()->create(['stripe_payment_intent_id' => 'pi_test_999', 'payment_status' => 'paid', 'total_cents' => 1000]);

        $event = Event::constructFrom([
            'id' => 'evt_5', 'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_test_2',
                'payment_intent' => 'pi_test_999',
                'amount_refunded' => 400,
                'refunds' => ['data' => [
                    ['id' => 're_test_2', 'amount' => 400],
                ]],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $this->assertSame('partially_refunded', $order->refresh()->payment_status);
    }
}
