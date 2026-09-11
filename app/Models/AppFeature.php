<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['app_id', 'title', 'description', 'icon', 'sort_order'])]
class AppFeature extends Model
{
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
