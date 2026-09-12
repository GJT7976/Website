<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'disk', 'path', 'filename', 'file_size', 'checksum_sha256', 'label', 'created_by'])]
class Backup extends Model
{
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function humanSize(): string
    {
        return number_format($this->file_size / 1048576, 2).' MB';
    }
}
