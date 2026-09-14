<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A one-time code emailed for the self-service device-management flow
 * (§10) — see App\Services\LicenseService::requestManagementCode()/
 * verifyManagementCode(). `code_hash` is sha256($rawCode); the raw code is
 * never persisted.
 */
#[Fillable(['license_id', 'email', 'code_hash', 'attempts', 'expires_at', 'consumed_at'])]
class LicenseVerificationCode extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture() && $this->attempts < 5;
    }
}
