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

    public function test_content_editor_can_view_sales(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.sales.index'))->assertOk();
    }
}
