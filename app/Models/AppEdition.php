<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'app_id', 'name', 'slug', 'description', 'price_cents', 'currency',
    'active', 'featured', 'sort_order', 'stripe_product_id', 'stripe_price_id',
])]
class AppEdition extends Model
{
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(EditionEntitlement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order');
    }

    /**
     * The platform codes this edition includes, e.g. ['android', 'windows'].
     * Used for display ("✓ Android  ✓ Windows") and for the immutable
     * order-item snapshot at purchase time.
     */
    public function includedPlatforms(): array
    {
        return $this->entitlements->map(fn (EditionEntitlement $e) => [
            'platform' => $e->platform->code,
            'platform_name' => $e->platform->name,
            'access_type' => $e->access_type,
        ])->all();
    }

    public function priceLabel(): string
    {
        return sprintf('$%s %s', number_format($this->price_cents / 100, 2), $this->currency);
    }
}
