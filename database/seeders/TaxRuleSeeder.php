<?php

namespace Database\Seeders;

use App\Models\TaxRule;
use Illuminate\Database\Seeder;

class TaxRuleSeeder extends Seeder
{
    /**
     * One starting tax rule for the business's home province, so checkout
     * has something real to calculate against locally. This is NOT a
     * verified, launch-ready configuration — per the spec (§21), current
     * Canadian/provincial tax requirements must be confirmed against
     * authoritative sources (e.g. the CRA) before this is relied on for
     * an actual sale. See the note field and Admin → Taxes.
     */
    public function run(): void
    {
        TaxRule::updateOrCreate(
            ['country' => 'CA', 'province' => 'ON', 'tax_name' => 'HST'],
            [
                'percentage' => 13.000,
                'effective_date' => '2020-01-01',
                'expiry_date' => null,
                'active' => true,
                'notes' => 'Seed default only — verify the current Ontario HST rate and this business\'s tax obligations (registration status, place-of-supply rules for digital goods, etc.) against CRA guidance before relying on this for a real launch.',
            ]
        );
    }
}
