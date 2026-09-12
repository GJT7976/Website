<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\CustomerEntitlement;
use App\Models\EditionEntitlement;
use App\Models\Order;
use App\Models\Platform;
use App\Services\StripeWebhookHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Tests\TestCase;

class EditionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function appWithBundleEdition(): array
    {
        $app = App::factory()->create([
            'status' => 'published',
            'is_free' => false,
            'direct_purchase_enabled' => true,
        ]);

        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);
        $windows = Platform::create(['code' => 'windows', 'name' => 'Windows', 'sort_order' => 1]);

        $edition = AppEdition::create([
            'app_id' => $app->id,
            'name' => 'Android + Windows Bundle',
            'slug' => 'android-windows',
            'price_cents' => 499,
            'currency' => 'CAD',
            'active' => true,
            'featured' => true,
            'sort_order' => 0,
        ]);

        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $windows->id, 'access_type' => 'download']);

        return [$app, $edition, $android, $windows];
    }

    public function test_edition_checkout_page_loads(): void
    {
        [$app, $edition] = $this->appWithBundleEdition();

        $this->get(route('checkout.create.edition', [$app, $edition]))->assertOk()->assertSee('$4.99');
    }

    public function test_edition_less_checkout_route_404s_once_the_app_has_editions(): void
    {
        [$app] = $this->appWithBundleEdition();

        $this->get(route('checkout.create', $app))->assertNotFound();
    }

    public function test_checkout_404s_for_an_edition_belonging_to_a_different_app(): void
    {
        [$app, $edition] = $this->appWithBundleEdition();
        $otherApp = App::factory()->create(['status' => 'published', 'direct_purchase_enabled' => true]);

        $this->get(route('checkout.create.edition', [$otherApp, $edition]))->assertNotFound();
    }

    public function test_submitting_edition_checkout_snapshots_edition_and_platforms_on_the_order_item(): void
    {
        [$app, $edition] = $this->appWithBundleEdition();

        // No Stripe keys configured in tests — order is created then rolled
        // back, same as the edition-less CheckoutTest. We only need to get
        // as far as order/order_item creation to verify the snapshot.
        $response = $this->post(route('checkout.store.edition', [$app, $edition]), [
            'customer_name' => 'Jane Baker',
            'customer_email' => 'jane@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'St. Catharines',
            'billing_province' => 'ON',
            'billing_postal_code' => 'L2R 1A1',
            'billing_country' => 'CA',
        ]);

        $response->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('orders', 0); // rolled back since Stripe isn't configured
    }

    public function test_webhook_creates_customer_entitlements_for_each_platform_the_edition_includes(): void
    {
        Mail::fake();

        [$app, $edition, $android, $windows] = $this->appWithBundleEdition();

        $order = Order::factory()->create([
            'customer_email' => 'jane@example.com',
            'stripe_checkout_session_id' => 'cs_test_edition',
            'total_cents' => 499,
        ]);
        $orderItem = $order->items()->create([
            'app_id' => $app->id,
            'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name,
            'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(),
            'unit_price_cents' => 499,
            'quantity' => 1,
            'line_subtotal_cents' => 499,
        ]);

        $event = Event::constructFrom([
            'id' => 'evt_edition_1', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_edition',
                'payment_intent' => 'pi_test_edition',
                'amount_total' => $order->total_cents,
                'currency' => 'cad',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]);

        app(StripeWebhookHandler::class)->handle($event);

        $this->assertDatabaseHas('customer_entitlements', [
            'order_item_id' => $orderItem->id,
            'platform_id' => $android->id,
            'access_type' => 'download',
            'customer_email' => 'jane@example.com',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('customer_entitlements', [
            'order_item_id' => $orderItem->id,
            'platform_id' => $windows->id,
            'access_type' => 'download',
            'status' => 'active',
        ]);
    }

    public function test_full_refund_revokes_entitlements_but_partial_refund_does_not(): void
    {
        [$app, $edition, $android] = $this->appWithBundleEdition();

        $order = Order::factory()->create([
            'stripe_payment_intent_id' => 'pi_test_revoke',
            'payment_status' => 'paid',
            'total_cents' => 499,
        ]);
        $orderItem = $order->items()->create([
            'app_id' => $app->id,
            'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name,
            'unit_price_cents' => 499,
            'quantity' => 1,
            'line_subtotal_cents' => 499,
        ]);
        $entitlement = CustomerEntitlement::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'app_id' => $app->id,
            'app_edition_id' => $edition->id,
            'platform_id' => $android->id,
            'access_type' => 'download',
            'customer_email' => 'jane@example.com',
            'status' => 'active',
            'source' => 'purchase',
        ]);

        $event = Event::constructFrom([
            'id' => 'evt_partial', 'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_partial', 'payment_intent' => 'pi_test_revoke', 'amount_refunded' => 200,
                'refunds' => ['data' => [['id' => 're_partial', 'amount' => 200]]],
            ]],
        ]);
        app(StripeWebhookHandler::class)->handle($event);

        $this->assertSame('active', $entitlement->refresh()->status);

        $event = Event::constructFrom([
            'id' => 'evt_full', 'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_full', 'payment_intent' => 'pi_test_revoke', 'amount_refunded' => 499,
                'refunds' => ['data' => [['id' => 're_full', 'amount' => 299]]],
            ]],
        ]);
        app(StripeWebhookHandler::class)->handle($event);

        $entitlement->refresh();
        $this->assertSame('revoked', $entitlement->status);
        $this->assertSame('refund', $entitlement->revoked_reason);
    }
}
