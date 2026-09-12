<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * The single write path for audit_logs (spec §33). Call sites are
 * explicit, one line each, at the point a mutation already commits —
 * matching this codebase's existing preference for explicit service
 * calls (see EntitlementService) over implicit model observers/events,
 * which this app uses nowhere.
 *
 * Deliberately exposes no way to update or delete a row: once written,
 * an audit_logs row is permanent. Don't add one.
 */
class AuditLogger
{
    public static function record(
        string $action,
        ?Model $resource = null,
        ?array $before = null,
        ?array $after = null,
        ?string $label = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $resource?->getMorphClass(),
            'auditable_id' => $resource?->getKey(),
            'resource_label' => $label ?? self::guessLabel($resource),
            'before' => $before,
            'after' => $after,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }

    private static function guessLabel(?Model $resource): ?string
    {
        return match (true) {
            $resource === null => null,
            isset($resource->name) => (string) $resource->name,
            isset($resource->order_number) => (string) $resource->order_number,
            isset($resource->email) => (string) $resource->email,
            isset($resource->filename) => (string) $resource->filename,
            default => (string) $resource->getKey(),
        };
    }
}
