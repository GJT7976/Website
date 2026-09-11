<?php

namespace App\Services;

use App\Models\TaxRule;
use Illuminate\Support\Carbon;

/**
 * Configurable, effective-dated tax calculation — never a single
 * hard-coded rate. See tax_rules migration / ARCHITECTURE.md and the
 * spec's explicit requirement (§21) that Canadian tax handling stay
 * database-backed and admin-editable rather than baked into code.
 */
class TaxCalculator
{
    /**
     * @return array<int, array{tax_rule_id: int, tax_name: string, percentage: string, amount_cents: int}>
     */
    public function calculate(int $subtotalCents, string $country, ?string $province, ?\DateTimeInterface $date = null): array
    {
        $date ??= Carbon::now();

        $rules = TaxRule::applicableTo($country, $province, $date)->get();

        return $rules->map(function (TaxRule $rule) use ($subtotalCents) {
            $amountCents = (int) round($subtotalCents * ((float) $rule->percentage / 100));

            return [
                'tax_rule_id' => $rule->id,
                'tax_name' => $rule->tax_name,
                'percentage' => (string) $rule->percentage,
                'amount_cents' => $amountCents,
            ];
        })->all();
    }

    public function totalCents(array $taxLines): int
    {
        return array_sum(array_column($taxLines, 'amount_cents'));
    }
}
