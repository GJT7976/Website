<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A template of what an AppEdition grants — e.g. "Android + Windows
 * Bundle" has two rows here (android/download, windows/download). See
 * CustomerEntitlement for the actual per-purchase grant created from these.
 */
#[Fillable(['app_edition_id', 'platform_id', 'access_type'])]
class EditionEntitlement extends Model
{
    public function edition(): BelongsTo
    {
        return $this->belongsTo(AppEdition::class, 'app_edition_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
