<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\CustomerEntitlement;
use App\Models\Order;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Collection;

/**
 * The only place CustomerEntitlement rows are created, revoked, or
 * restored — keeps §35/§36 (refund/override policy) in one place instead
 * of scattered across the webhook handler and admin controllers.
 *
 * Every grant/revoke/restore stamps who did it and why directly on the
 * row (granted_by/revoked_by/revoked_reason/timestamps). That is the
 * deliberate substitute for a general-purpose audit_logs table, which
 * CLAUDE.md/ARCHITECTURE.md defer to a later phase.
 */
class EntitlementService
{
    /**
     * Called once an order is confirmed paid (StripeWebhookHandler only —
     * never from a checkout-success request). Creates one entitlement per
     * platform/access-type the purchased edition grants. Order items with
     * no app_edition_id (legacy, edition-less purchases) are skipped —
     * there is nothing to base a download/web-access grant on.
     */
    public function createFromOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->app_edition_id) {
                continue;
            }

            $edition = AppEdition::with('entitlements.platform')->find($item->app_edition_id);

            if (! $edition) {
                continue;
            }

            foreach ($edition->entitlements as $grant) {
                CustomerEntitlement::query()->firstOrCreate(
                    [
                        'order_item_id' => $item->id,
                        'platform_id' => $grant->platform_id,
                        'access_type' => $grant->access_type,
                    ],
                    [
                        'order_id' => $order->id,
                        'app_id' => $item->app_id,
                        'app_edition_id' => $edition->id,
                        'customer_email' => mb_strtolower(trim($order->customer_email)),
                        'status' => 'active',
                        'source' => 'purchase',
                    ]
                );
            }
        }
    }

    /**
     * Called only on a *full* refund (§35) — a partial refund leaves
     * access intact. Idempotent: safe to call from both the Stripe webhook
     * and an admin-initiated refund without double-processing.
     */
    public function revokeForOrder(Order $order, string $reason): void
    {
        CustomerEntitlement::query()
            ->where('order_id', $order->id)
            ->active()
            ->get()
            ->each(fn (CustomerEntitlement $entitlement) => $this->revoke($entitlement, null, $reason));
    }

    /**
     * Manual admin grant (§36) — not tied to any order. Pass either an
     * edition (grants everything that edition includes) or a single
     * platform + access type.
     */
    public function grant(App $app, string $email, ?int $grantedBy, ?AppEdition $edition = null, ?Platform $platform = null, ?string $accessType = null): Collection
    {
        $email = mb_strtolower(trim($email));
        $created = new Collection;

        if ($edition) {
            $edition->loadMissing('entitlements.platform');

            foreach ($edition->entitlements as $grantTemplate) {
                $created->push(CustomerEntitlement::create([
                    'app_id' => $app->id,
                    'app_edition_id' => $edition->id,
                    'platform_id' => $grantTemplate->platform_id,
                    'access_type' => $grantTemplate->access_type,
                    'customer_email' => $email,
                    'status' => 'active',
                    'source' => 'admin_grant',
                    'granted_by' => $grantedBy,
                ]));
            }

            return $created;
        }

        $created->push(CustomerEntitlement::create([
            'app_id' => $app->id,
            'platform_id' => $platform->id,
            'access_type' => $accessType,
            'customer_email' => $email,
            'status' => 'active',
            'source' => 'admin_grant',
            'granted_by' => $grantedBy,
        ]));

        return $created;
    }

    public function revoke(CustomerEntitlement $entitlement, ?int $revokedBy, ?string $reason): void
    {
        if ($entitlement->status === 'revoked') {
            return;
        }

        $entitlement->update([
            'status' => 'revoked',
            'revoked_by' => $revokedBy,
            'revoked_reason' => $reason,
            'revoked_at' => now(),
        ]);
    }

    public function restore(CustomerEntitlement $entitlement, ?int $restoredBy): void
    {
        $entitlement->update([
            'status' => 'active',
            'granted_by' => $entitlement->granted_by ?? $restoredBy,
            'revoked_by' => null,
            'revoked_reason' => null,
            'revoked_at' => null,
        ]);
    }

    /**
     * Active entitlements for the My Downloads page, eager-loaded for
     * display — grouped by app in the controller.
     */
    public function resolveForEmail(string $email): Collection
    {
        return CustomerEntitlement::query()
            ->forEmail($email)
            ->active()
            ->with(['app', 'edition', 'platform', 'order'])
            ->get();
    }
}
