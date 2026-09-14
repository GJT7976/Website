<?php

namespace Tests\Feature;

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
 * §20: the checkout success page displays the issued license key(s) once
 * the webhook has confirmed payment — never before (see CLAUDE.md's
 * payment-confirmation rule).
 */
class CheckoutSuccessLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_page_shows_the_license_key_once_the_webhook_has_confirmed_payment(): void
    {
        Mail::fake();

        $app = App::factory()->create();
        $edition = AppEdition::create([
            'app_id' => $app->id, 'name' => 'Android', 'slug' => 'android-'.uniqid(),
            'price_cents' => 299, 'currency' => 'USD', 'active' => true, 'featured' => false, 'sort_order' => 0,
        ]);
        $android = Platform::firstOrCreate(['code' => 'android'], ['name' => 'Android', 'sort_order' => 0]);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);

        $order = Order::factory()->create(['stripe_checkout_session_id' => 'cs_success_test', 'total_cents' => 299, 'currency' => 'USD']);
        $order->items()->create([
            'app_id' => $app->id, 'app_edition_id' => $edition->id,
            'app_name_snapshot' => $app->name, 'edition_name_snapshot' => $edition->name,
            'included_platforms_snapshot' => $edition->includedPlatforms(), 'license_label_snapshot' => 'PRO',
            'unit_price_cents' => 299, 'quantity' => 1, 'line_subtotal_cents' => 299,
        ]);

        app(StripeWebhookHandler::class)->handle(Event::constructFrom([
            'id' => 'evt_success_test', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_success_test', 'payment_intent' => 'pi_success_test',
                'amount_total' => 299, 'currency' => 'usd', 'metadata' => [],
            ]],
        ]));

        $license = License::first();

        $response = $this->get(route('checkout.success', $app).'?session_id=cs_success_test');

        $response->assertOk();
        $response->assertSee($license->license_key_encrypted);
    }
}
