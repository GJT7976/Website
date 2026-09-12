<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable "who did what" record (spec §33). Only ever created via
 * App\Services\AuditLogger::record() — never updated, never deleted from
 * any admin action. See that service's docblock for why this exists
 * alongside (not instead of) CustomerEntitlement's own granted_by/
 * revoked_by stamping.
 */
#[Fillable([
    'user_id', 'action', 'auditable_type', 'auditable_id',
    'resource_label', 'before', 'after', 'ip_address', 'user_agent',
])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
        ];
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
