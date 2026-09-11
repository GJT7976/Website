<?php

namespace App\Models;

use Database\Factories\TaxRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['country', 'province', 'tax_name', 'percentage', 'effective_date', 'expiry_date', 'active', 'notes'])]
class TaxRule extends Model
{
    /** @use HasFactory<TaxRuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:3',
            'effective_date' => 'date',
            'expiry_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * Rules that could apply to a given country/province on a given date —
     * province-specific rules and country-wide rules (province null) both
     * match. Never returns a rule the caller didn't explicitly find active
     * and in date, so "no rule" always means $0 tax, never a guess.
     */
    public function scopeApplicableTo(Builder $query, string $country, ?string $province, \DateTimeInterface $date): Builder
    {
        return $query->where('country', $country)
            ->where(function (Builder $q) use ($province) {
                $q->whereNull('province')->orWhere('province', $province);
            })
            ->where('active', true)
            ->whereDate('effective_date', '<=', $date)
            ->where(function (Builder $q) use ($date) {
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', $date);
            });
    }
}
