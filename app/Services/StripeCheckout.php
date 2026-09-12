<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\StripeClient;

/**
 * Thin wrapper around the Stripe SDK. Kept separate from CheckoutController
 * so tests can fake/skip just this class instead of mocking the whole SDK,
 * and so there is exactly one place that talks to Stripe's API.
 */
class StripeCheckout
{
    public function client(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret')) && filled(config('services.stripe.key'));
    }

    /**
     * Create a Checkout Session for a single-item order and return its
     * hosted checkout URL. Uses inline price_data — no need to pre-create
     * Stripe Price objects for every app. When an edition was purchased,
     * $appEditionId is stamped into metadata (§14) so the webhook handler
     * can look it up without trusting anything else from Stripe.
     */
    public function createSessionUrl(Order $order, string $appName, int $amountCents, string $currency, string $successUrl, string $cancelUrl, ?int $appEditionId = null): string
    {
        $metadata = [
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'app_edition_id' => $appEditionId !== null ? (string) $appEditionId : '',
        ];

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $order->customer_email,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => ['name' => $appName],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'metadata' => $metadata,
            // Also stamped onto the PaymentIntent itself (not just the
            // Checkout Session) so a payment_intent.payment_failed event —
            // which can fire before checkout.session.completed ever would —
            // can still be matched back to this order.
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
            'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
        ], ['api_key' => config('services.stripe.secret')]);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session->url;
    }

    public function refund(string $paymentIntentId, ?int $amountCents = null): Refund
    {
        $params = ['payment_intent' => $paymentIntentId];

        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }

        return $this->client()->refunds->create($params);
    }
}
