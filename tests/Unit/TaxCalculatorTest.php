<?php

namespace Tests\Unit;

use App\Models\TaxRule;
use App\Services\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaxCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_applies_a_matching_provincial_rate(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'ON', 'tax_name' => 'HST', 'percentage' => 13]);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'ON');

        $this->assertCount(1, $lines);
        $this->assertSame('HST', $lines[0]['tax_name']);
        $this->assertSame(1300, $lines[0]['amount_cents']);
    }

    public function test_country_wide_rule_applies_when_province_is_null(): void
    {
        TaxRule::factory()->create(['country' => 'US', 'province' => null, 'tax_name' => 'Sales Tax', 'percentage' => 5]);

        $lines = (new TaxCalculator)->calculate(10000, 'US', 'CA');

        $this->assertCount(1, $lines);
        $this->assertSame(500, $lines[0]['amount_cents']);
    }

    public function test_no_matching_rule_means_zero_tax(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'ON']);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'BC');

        $this->assertCount(0, $lines);
        $this->assertSame(0, (new TaxCalculator)->totalCents($lines));
    }

    public function test_inactive_rule_is_ignored(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'ON', 'active' => false]);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'ON');

        $this->assertCount(0, $lines);
    }

    public function test_expired_rule_is_ignored(): void
    {
        TaxRule::factory()->create([
            'country' => 'CA', 'province' => 'ON',
            'effective_date' => '2020-01-01', 'expiry_date' => '2021-01-01',
        ]);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'ON', Carbon::parse('2022-01-01'));

        $this->assertCount(0, $lines);
    }

    public function test_future_rule_not_yet_effective_is_ignored(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'ON', 'effective_date' => '2030-01-01']);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'ON', Carbon::parse('2026-01-01'));

        $this->assertCount(0, $lines);
    }

    public function test_multiple_stacked_rules_all_apply(): void
    {
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'QC', 'tax_name' => 'GST', 'percentage' => 5]);
        TaxRule::factory()->create(['country' => 'CA', 'province' => 'QC', 'tax_name' => 'QST', 'percentage' => 9.975]);

        $lines = (new TaxCalculator)->calculate(10000, 'CA', 'QC');

        $this->assertCount(2, $lines);
        $this->assertSame(1498, (new TaxCalculator)->totalCents($lines)); // 500 (5% GST) + 998 (9.975% QST, rounded)
    }
}
