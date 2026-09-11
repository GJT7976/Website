<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeWebhookHandler $handler) {}

    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (blank($secret)) {
            Log::warning('Stripe webhook received but STRIPE_WEBHOOK_SECRET is not configured — ignoring.');

            return response('Webhook not configured', 501);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret,
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        $this->handler->handle($event);

        return response('OK', 200);
    }
}
