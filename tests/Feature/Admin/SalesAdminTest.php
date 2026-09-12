<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_index_shows_totals_for_paid_orders_in_period(): void
    {
        $admin = User::factory()->owner()->create();
        Order::factory()->create(['payment_status' => 'paid', 'total_cents' => 1000, 'tax_cents' => 100]);
        Order::factory()->create(['payment_status' => 'pending', 'total_cents' => 500]);

        $response = $this->actingAs($admin)->get(route('admin.sales.index', ['period' => 'month']));

        $response->assertOk();
        $response->assertSee('$10.00'); // gross for the one paid order
    }

    public function test_sales_csv_export_is_downloadable(): void
    {
        $admin = User::factory()->owner()->create();
        Order::factory()->create(['payment_status' => 'paid', 'order_number' => 'NIA-TEST1']);

        $response = $this->actingAs($admin)->get(route('admin.sales.export', ['period' => 'month']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('NIA-TEST1', $response->getContent());
    }

    public function test_sales_csv_export_includes_edition_and_platform_columns(): void
    {
        $admin = User::factory()->owner()->create();
        $order = Order::factory()->create(['payment_status' => 'paid', 'order_number' => 'NIA-TEST2', 'billing_province' => 'ON', 'billing_country' => 'CA']);
        $order->items()->create([
            'app_name_snapshot' => 'Hummus House',
            'edition_name_snapshot' => 'Android + Windows Bundle',
            'included_platforms_snapshot' => [['platform' => 'android', 'platform_name' => 'Android', 'access_type' => 'download']],
            'unit_price_cents' => 499,
            'quantity' => 1,
            'line_subtotal_cents' => 499,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.sales.export', ['period' => 'month']));

        $csv = $response->getContent();
        $this->assertStringContainsString('Edition,Platforms Included', $csv);
        $this->assertStringContainsString('Android + Windows Bundle', $csv);
        $this->assertStringContainsString('Android', $csv);
        $this->assertStringContainsString('ON', $csv);
    }

    public function test_content_editor_can_view_sales(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.sales.index'))->assertOk();
    }
}
