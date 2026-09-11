<?php

namespace Tests\Feature\Admin;

use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxRuleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_tax_rule(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('admin.tax-rules.store'), [
            'country' => 'ca',
            'province' => 'BC',
            'tax_name' => 'GST + PST',
            'percentage' => 12,
            'effective_date' => '2026-01-01',
            'active' => '1',
        ])->assertRedirect(route('admin.tax-rules.index'));

        $this->assertDatabaseHas('tax_rules', ['country' => 'CA', 'province' => 'BC', 'tax_name' => 'GST + PST']);
    }

    public function test_content_editor_cannot_manage_tax_rules(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.tax-rules.index'))->assertForbidden();
    }

    public function test_owner_can_update_and_delete_a_tax_rule(): void
    {
        $owner = User::factory()->owner()->create();
        $rule = TaxRule::factory()->create();

        $this->actingAs($owner)->put(route('admin.tax-rules.update', $rule), [
            'country' => 'CA', 'province' => 'ON', 'tax_name' => 'HST', 'percentage' => 15,
            'effective_date' => '2020-01-01', 'active' => '1',
        ]);
        $this->assertSame('15.000', $rule->fresh()->percentage);

        $this->actingAs($owner)->delete(route('admin.tax-rules.destroy', $rule));
        $this->assertDatabaseMissing('tax_rules', ['id' => $rule->id]);
    }
}
