<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One activated device against a License. Created/updated only by
 * App\Services\LicenseService — see that class for the two-device rule
 * (§9) and self-service reset abuse limiting (§11).
 */
#[Fillable([
    'license_id', 'platform', 'device_identifier_hash', 'label', 'app_version',
    'status', 'activated_at', 'last_validated_at', 'deactivated_at', 'deactivated_reason',
])]
class LicenseDevice extends Model
{
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'last_validated_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
