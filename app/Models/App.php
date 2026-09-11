<?php

namespace App\Models;

use Database\Factories\AppFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'slug', 'tagline', 'short_description', 'long_description', 'category_id',
    'version', 'release_date', 'updated_on',
    'price_cents', 'sale_price_cents', 'currency', 'is_free',
    'google_play_url', 'microsoft_store_url', 'apple_url',
    'direct_purchase_enabled', 'stripe_product_id', 'stripe_price_id',
    'documentation_url', 'privacy_policy_url', 'support_info', 'system_requirements',
    'status', 'is_featured', 'featured_order', 'seo_title', 'seo_description',
    'demo_enabled', 'demo_type', 'demo_url', 'demo_version', 'demo_instructions',
    'demo_warning', 'demo_reset_mode',
])]
class App extends Model
{
    /** @use HasFactory<AppFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'updated_on' => 'date',
            'is_free' => 'boolean',
            'direct_purchase_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'demo_enabled' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AppCategory::class, 'category_id');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class)->orderBy('sort_order');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'app_media')
            ->withPivot(['type', 'sort_order', 'caption'])
            ->orderByPivot('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(AppFeature::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->where('published', true)->orderBy('sort_order');
    }

    public function supportRequests(): HasMany
    {
        return $this->hasMany(SupportRequest::class);
    }

    public function icon(): ?Media
    {
        return $this->media->firstWhere('pivot.type', 'icon');
    }

    public function featureGraphic(): ?Media
    {
        return $this->media->firstWhere('pivot.type', 'feature_graphic');
    }

    public function screenshots()
    {
        return $this->media->where('pivot.type', 'screenshot')->sortBy('pivot.sort_order')->values();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->orderBy('featured_order');
    }

    public function scopeDemoEnabled(Builder $query): Builder
    {
        return $query->where('demo_enabled', true);
    }

    /**
     * Effective price in minor units, respecting a sale price when set.
     */
    public function effectivePriceCents(): ?int
    {
        if ($this->is_free) {
            return 0;
        }

        return $this->sale_price_cents ?? $this->price_cents;
    }

    /**
     * Human-readable price, e.g. "$19.99 CAD" or "Free".
     */
    public function priceLabel(): string
    {
        if ($this->is_free) {
            return 'Free';
        }

        $cents = $this->effectivePriceCents();

        if ($cents === null) {
            return 'Contact for pricing';
        }

        return sprintf('$%s %s', number_format($cents / 100, 2), $this->currency ?? 'CAD');
    }

    public function onSale(): bool
    {
        return ! $this->is_free
            && $this->sale_price_cents !== null
            && $this->price_cents !== null
            && $this->sale_price_cents < $this->price_cents;
    }
}
