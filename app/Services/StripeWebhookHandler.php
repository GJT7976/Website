<?php

namespace App\Services;

use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;

/**
 * Pure event-handling logic, deliberately free of any Stripe API calls or
 * HTTP concerns — StripeWebhookController verifies the signature and hands
 * this class a constructed \Stripe\Event, so tests can call it directly
 * with \Stripe\Event::constructFrom([...]) and never touch the network.
 *
 * Never trust a browser redirect back to a "success" URL by itself — an
 * order is only ever marked paid/refunded/failed from here. Entitlements
 * and licenses ride along with that same rule: EntitlementService and
 * LicenseService only ever create a grant/license once this handler has
 * confirmed payment, and only ever revoke one once it has confirmed a
 * full refund or a chargeback dispute.
 */
class StripeWebhookHandler
{
    public function __construct(
        private EntitlementService $entitlements,
        private LicenseService $licenses,
    ) {}

    public function handle(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'charge.refunded' => $this->handleChargeRefunded($event),
            'charge.dispute.created' => $this->handleDisputeCreated($event),
            default => Log::info("Stripe webhook: unhandled event type [{$event->type}]"),
        };
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $order = $this->findOrder($session->metadata->order_id ?? null, $session->id ?? null);

        if (! $order) {
            Log::warning('Stripe webhook: checkout.session.completed for unknown order', ['session_id' => $session->id ?? null]);

            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'stripe_payment_intent_id' => $session->payment_intent ?? $order->stripe_payment_intent_id,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_payment_id' => $session->payment_intent ?? null,
            'amount_cents' => $session->amount_total ?? $order->total_cents,
            'currency' => strtoupper($session->currency ?? $order->currency),
            'status' => 'succeeded',
            'raw_payload' => $event->toArray(),
        ]);

        $this->entitlements->createFromOrder($order);
        $licenses = $this->licenses->createFromOrder($order);

        try {
            Mail::to($order->customer_email)->send(new OrderReceipt($order, $licenses));
        } catch (\Throwable $e) {
            Log::warning('Order receipt email failed to send.', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    private function handlePaymentFailed(Event $event): void
    {
        $intent = $event->data->object;
        $order = $this->findOrder($intent->metadata->order_id ?? null, null, $intent->id ?? null);

        if (! $order) {
            Log::warning('Stripe webhook: payment_intent.payment_failed for unknown order', ['payment_intent_id' => $intent->id ?? null]);

            return;
        }

        $order->update(['payment_status' => 'failed']);
    }

    private function handleChargeRefunded(Event $event): void
    {
        $charge = $event->data->object;
        $order = $this->findOrder(null, null, $charge->payment_intent ?? null);

        if (! $order) {
            Log::warning('Stripe webhook: charge.refunded for unknown order', ['payment_intent_id' => $charge->payment_intent ?? null]);

            return;
        }

        $latestRefund = $charge->refunds->data[0] ?? null;
        $payment = $order->payments()->where('provider_payment_id', $charge->payment_intent ?? null)->first();

        Refund::create([
            'order_id' => $order->id,
            'payment_id' => $payment?->id,
            'stripe_refund_id' => $latestRefund->id ?? null,
            'amount_cents' => $latestRefund->amount ?? ($charge->amount_refunded ?? 0),
            'reason' => $latestRefund->reason ?? null,
            'administrator_id' => null, // came from Stripe directly, not an admin action here
            'status' => 'succeeded',
        ]);

        $fullyRefunded = ($charge->amount_refunded ?? 0) >= $order->total_cents;
        $order->update(['payment_status' => $fullyRefunded ? 'refunded' : 'partially_refunded']);

        // §35: a fully refunded direct purchase no longer grants new
        // downloads/access unless an admin overrides it afterward. A
        // partial refund leaves entitlements/licenses untouched.
        if ($fullyRefunded) {
            $this->entitlements->revokeForOrder($order, 'refund');
            $this->licenses->revokeForOrder($order, 'refunded');
        }
    }

    /**
     * A chargeback dispute (§24). This deliberately doesn't touch
     * `orders.payment_status` — that enum's values (pending/paid/failed/
     * refunded/partially_refunded) predate license support and changing
     * it is outside this change's scope — but it does immediately revoke
     * entitlements and licenses, same as a full refund, so the app
     * returns to locked on its next online validation.
     */
    private function handleDisputeCreated(Event $event): void
    {
        $dispute = $event->data->object;
        $order = $this->findOrder(null, null, $dispute->payment_intent ?? null);

        if (! $order) {
            Log::warning('Stripe webhook: charge.dispute.created for unknown order', ['payment_intent_id' => $dispute->payment_intent ?? null]);

            return;
        }

        $this->entitlements->revokeForOrder($order, 'chargeback');
        $this->licenses->revokeForOrder($order, 'chargeback');
    }

    private function findOrder(?string $orderId, ?string $sessionId = null, ?string $paymentIntentId = null): ?Order
    {
        if ($orderId) {
            return Order::find($orderId);
        }

        if ($sessionId) {
            return Order::where('stripe_checkout_session_id', $sessionId)->first();
        }

        if ($paymentIntentId) {
            return Order::where('stripe_payment_intent_id', $paymentIntentId)->first();
        }

        return null;
    }
}
