<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A permanent, one-time-purchase PRO license for a Flutter app sold
 * directly from this website (never Google Play). Created only by
 * App\Services\LicenseService::createFromOrder(), which is the single
 * place a License is ever generated — mirrors how CustomerEntitlement is
 * only ever written by EntitlementService. `license_key_hash` is
 * sha256($rawKey); the raw key itself is never persisted anywhere.
 */
#[Fillable([
    'app_id', 'app_edition_id', 'order_id', 'order_item_id', 'license_key_hash',
    'license_key_encrypted', 'customer_name', 'customer_email', 'platform_entitlement',
    'price_paid_cents', 'currency', 'transaction_id', 'payment_provider',
    'purchase_date', 'maximum_devices', 'status',
])]
class License extends Model
{
    protected $hidden = ['license_key_encrypted', 'license_key_hash'];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'datetime',
            'license_key_encrypted' => 'encrypted',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(AppEdition::class, 'app_edition_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(LicenseDevice::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LicenseEvent::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function activeDevices(): HasMany
    {
        return $this->devices()->where('status', 'active');
    }

    /**
     * Whether this license's platform_entitlement (§7) permits the given
     * platform code ('android'|'windows') at all — independent of whether
     * a device slot is available.
     */
    public function permitsPlatform(string $platform): bool
    {
        return match ($this->platform_entitlement) {
            'android_only' => $platform === 'android',
            'windows_only' => $platform === 'windows',
            'android_windows_bundle' => in_array($platform, ['android', 'windows'], true),
            default => false,
        };
    }
}
