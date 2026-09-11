<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'email', 'subject', 'app_id', 'message', 'status', 'ip_address'])]
class SupportRequest extends Model
{
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
