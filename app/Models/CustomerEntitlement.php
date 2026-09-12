<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The actual per-purchase (or admin-granted) access record. Created by
 * App\Services\EntitlementService, never directly — see that class for how
 * these are granted/revoked/restored, and why a full audit_logs table
 * isn't used for the admin-override trail (CLAUDE.md defers that table to
 * a later phase; granted_by/revoked_by/revoked_reason on this row is the
 * deliberately-scoped substitute).
 */
#[Fillable([
    'order_id', 'order_item_id', 'app_id', 'app_edition_id', 'platform_id', 'access_type',
    'customer_email', 'status', 'source', 'granted_by', 'revoked_by', 'revoked_reason', 'revoked_at',
])]
class CustomerEntitlement extends Model
{
    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(AppEdition::class, 'app_edition_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->where('customer_email', mb_strtolower(trim($email)));
    }

    /**
     * The current authorized release for this entitlement's platform —
     * never a specific historical file (§24: an entitlement belongs to the
     * platform/edition, not to one physical file).
     */
    public function currentRelease(): ?AppRelease
    {
        if ($this->access_type !== 'download') {
            return null;
        }

        return AppRelease::query()
            ->where('app_id', $this->app_id)
            ->where('platform_id', $this->platform_id)
            ->where('customer_downloadable', true)
            ->current()
            ->latest('released_at')
            ->first();
    }
}
