<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only activation/validation/reset history for a License — see the
 * create_license_events_table migration for why this is kept separate
 * from the general audit_logs table.
 */
#[Fillable(['license_id', 'license_device_id', 'type', 'platform', 'ip_address', 'created_at'])]
class LicenseEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(LicenseDevice::class, 'license_device_id');
    }
}
