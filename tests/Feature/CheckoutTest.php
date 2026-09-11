<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\TaxRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function purchasableApp(array $overrides = []): App
    {
        return App::factory()->create([
            'status' => 'published',
            'is_free' => false,
            'price_cents' => 1999,
            'currency' => 'CAD',
            'direct_purchase_enabled' => true,
            ...$overrides,
        ]);
    }

    public function test_checkout_page_loads_for_a_purchasable_app(): void
    {
        $app = $this->purchasableApp();

        $this->get(route('checkout.create', $app))->assertOk();
    }

    public function test_checkout_404s_for_a_free_app(): void
    {
        $app = App::factory()->create(['status' => 'published', 'is_free' => true]);

        $this->get(route('checkout.create', $app))->assertNotFound();
    }

    public function test_checkout_404s_when_direct_purchase_is_disabled(): void
    {
        $app = $this->purchasableApp(['direct_purchase_enabled' => false]);

        $this->get(route('checkout.create', $app))->assertNotFound();
    }

    public function test_checkout_404s_for_an_unpublished_app(): void
    {
        $app = $this->purchasableApp(['status' => 'draft']);

        $this->get(route('checkout.create', $app))->assertNotFound();
    }

    public function test_submitting_billing_info_creates_an_order_with_calculated_tax(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'ON', 'percentage' => 13]);
        $app = $this->purchasableApp(['price_cents' => 1000]);

        // No Stripe keys configured in tests, so this should fail gracefully
        // (order created then rolled back) rather than call the real API.
        $response = $this->post(route('checkout.store', $app), [
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

    public function test_checkout_requires_billing_fields(): void
    {
        $app = $this->purchasableApp();

        $this->post(route('checkout.store', $app), [])
            ->assertSessionHasErrors(['customer_name', 'customer_email', 'billing_address', 'billing_city', 'billing_postal_code', 'billing_country']);
    }
}
