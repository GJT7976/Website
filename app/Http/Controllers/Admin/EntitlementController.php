<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AppEdition;
use App\Models\CustomerEntitlement;
use App\Models\Platform;
use App\Services\EntitlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin overrides (§36): manually grant, revoke, or restore access. Every
 * action goes through EntitlementService so granted_by/revoked_by/
 * revoked_reason is always stamped — see that class for why this replaces
 * a general audit_logs table for this deliberately-scoped case.
 */
class EntitlementController extends Controller
{
    public function __construct(private EntitlementService $entitlements) {}

    public function store(Request $request, App $app): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'app_edition_id' => ['nullable', 'exists:app_editions,id'],
            'platform_id' => ['required_without:app_edition_id', 'nullable', 'exists:platforms,id'],
            'access_type' => ['required_without:app_edition_id', 'nullable', 'in:download,web_access'],
        ]);

        $edition = ! empty($data['app_edition_id']) ? AppEdition::find($data['app_edition_id']) : null;
        $platform = ! empty($data['platform_id']) ? Platform::find($data['platform_id']) : null;

        $this->entitlements->grant($app, $data['email'], Auth::id(), $edition, $platform, $data['access_type'] ?? null);

        return back()->with('status', "Access granted to {$data['email']}.");
    }

    public function revoke(Request $request, CustomerEntitlement $entitlement): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? 'admin_override';

        $this->entitlements->revoke($entitlement, Auth::id(), $reason);

        return back()->with('status', 'Access revoked.');
    }

    public function restore(CustomerEntitlement $entitlement): RedirectResponse
    {
        $this->entitlements->restore($entitlement, Auth::id());

        return back()->with('status', 'Access restored.');
    }
}
