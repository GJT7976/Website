<?php

namespace Database\Factories;

use App\Models\TaxRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRule>
 */
class TaxRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country' => 'CA',
            'province' => 'ON',
            'tax_name' => 'HST',
            'percentage' => 13.000,
            'effective_date' => '2020-01-01',
            'expiry_date' => null,
            'active' => true,
        ];
    }
}
