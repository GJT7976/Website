<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'customer_name', 'customer_email', 'billing_company',
    'billing_address', 'billing_city', 'billing_province', 'billing_postal_code',
    'billing_country', 'subtotal_cents', 'discount_cents', 'tax_cents', 'total_cents',
    'currency', 'payment_provider', 'stripe_checkout_session_id', 'stripe_payment_intent_id',
    'payment_status', 'order_status', 'notes',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function taxLines(): HasMany
    {
        return $this->hasMany(SalesTaxLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(CustomerEntitlement::class);
    }

    public function scopeBetween(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function totalRefundedCents(): int
    {
        return (int) $this->refunds()->where('status', 'succeeded')->sum('amount_cents');
    }

    public function netTotalCents(): int
    {
        return $this->total_cents - $this->totalRefundedCents();
    }

    public function moneyLabel(int $cents): string
    {
        return sprintf('$%s %s', number_format($cents / 100, 2), $this->currency);
    }
}
