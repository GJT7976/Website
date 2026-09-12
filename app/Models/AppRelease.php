<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'app_id', 'platform_id', 'version', 'build_number', 'release_notes',
    'min_os', 'released_at', 'is_current', 'disk', 'path', 'original_filename',
    'file_size', 'checksum_sha256', 'mime_type', 'customer_downloadable', 'uploaded_by',
])]
class AppRelease extends Model
{
    protected function casts(): array
    {
        return [
            'released_at' => 'date',
            'is_current' => 'boolean',
            'customer_downloadable' => 'boolean',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function fileSizeLabel(): string
    {
        $bytes = $this->file_size;

        return match (true) {
            $bytes >= 1024 * 1024 * 1024 => number_format($bytes / (1024 * 1024 * 1024), 2).' GB',
            $bytes >= 1024 * 1024 => number_format($bytes / (1024 * 1024), 1).' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 1).' KB',
            default => $bytes.' B',
        };
    }
}
