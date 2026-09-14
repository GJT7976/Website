<?php

namespace Tests\Feature;

use App\Mail\OrderReceipt;
use App\Models\App;
use App\Models\AppEdition;
use App\Models\EditionEntitlement;
use App\Models\License;
use App\Models\Order;
use App\Models\Platform;
use App\Services\StripeWebhookHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Tests\TestCase;

/**
 * License issuance/revocation as a side effect of the Stripe webhook —
 * mirrors StripeWebhookHandlerTest's no-network style. Covers §5's replay
 * protection (a re-delivered checkout.session.completed must not issue a
 * second license) and §24's refund/chargeback handling.
 */
class StripeWebhookLicenseTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrderWithAndroidEdition(): Order
    {
        $app = App::factory()->create();
        $edition = AppEdition::create([
            'app_id' => $app->id, 'name' => 'Android', 'slug' => 'android-'.uniqid(),
            'price_cents' => 299, 'currency' => 'USD', 'active' => true, 'featured' => false, 'sort_order' => 0,
        ]);
        $android = Platform::firstOrCreate(['code' => 'android'], ['name' => 'Android', 'sort_order' => 0]);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);

        $order = Order::factory()->create(['stripe_checkout_session_id' => 'cs_test_lic_1', 'total_cents' => 299, 'currency' => 'USD']);
        $order->items()->create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name, 'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(), 'license_label_snapshot' => 'PRO',
            'unit_price_cents' => 299, 'quantity' => 1, 'line_subtotal_cents' => 299,
        ]);

        return $order;
    }

    private function checkoutCompletedEvent(string $sessionId, int $amount): Event
    {
        return Event::constructFrom([
            'id' => 'evt_lic_'.uniqid(), 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => $sessionId, 'payment_intent' => 'pi_lic_'.uniqid(),
                'amount_total' => $amount, 'currency' => 'usd',
                'metadata' => [],
            ]],
        ]);
    }

    public function test_a_confirmed_checkout_issues_a_license_and_emails_it(): void
    {
        Mail::fake();
        $order = $this->paidOrderWithAndroidEdition();

        app(StripeWebhookHandler::class)->handle($this->checkoutCompletedEvent('cs_test_lic_1', 299));

        $this->assertDatabaseCount('licenses', 1);
        $license = License::first();
        $this->assertSame($order->id, $license->order_id);
        $this->assertSame('android_only', $license->platform_entitlement);

        Mail::assertSent(OrderReceipt::class, function ($mail) use ($license) {
            return $mail->licenses->contains('id', $license->id);
        });
    }

    public function test_a_replayed_webhook_does_not_issue_a_second_license(): void
    {
        Mail::fake();
        $this->paidOrderWithAndroidEdition();

        $handler = app(StripeWebhookHandler::class);
        $handler->handle($this->checkoutCompletedEvent('cs_test_lic_1', 299));
        $handler->handle($this->checkoutCompletedEvent('cs_test_lic_1', 299));

        $this->assertDatabaseCount('licenses', 1);
    }

    public function test_full_refund_revokes_the_license(): void
    {
        Mail::fake();
        $order = $this->paidOrderWithAndroidEdition();
        app(StripeWebhookHandler::class)->handle($this->checkoutCompletedEvent('cs_test_lic_1', 299));
        $order->refresh();

        $event = Event::constructFrom([
            'id' => 'evt_refund_1', 'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_1', 'payment_intent' => $order->stripe_payment_intent_id,
                'amount_refunded' => 299,
                'refunds' => ['data' => [['id' => 're_1', 'amount' => 299]]],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $this->assertSame('refunded', License::first()->refresh()->status);
    }

    public function test_a_dispute_revokes_the_license(): void
    {
        Mail::fake();
        $order = $this->paidOrderWithAndroidEdition();
        app(StripeWebhookHandler::class)->handle($this->checkoutCompletedEvent('cs_test_lic_1', 299));
        $order->refresh();

        $event = Event::constructFrom([
            'id' => 'evt_dispute_1', 'type' => 'charge.dispute.created',
            'data' => ['object' => ['id' => 'du_1', 'payment_intent' => $order->stripe_payment_intent_id]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $this->assertSame('chargeback', License::first()->refresh()->status);
    }
}
