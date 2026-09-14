<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseDevice;
use App\Services\AuditLogger;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * §23's admin dashboard for licenses. Every mutation is delegated to
 * LicenseService (the single place License/LicenseDevice rows change) and
 * stamped to audit_logs via AuditLogger — the same pattern
 * Admin\OrderController/EditionController/BackupController already use
 * (see ARCHITECTURE.md's note on why EntitlementController is the one
 * exception to that, which doesn't apply here).
 */
class LicenseController extends Controller
{
    public function __construct(private LicenseService $licenses) {}

    public function index(Request $request): View
    {
        $query = License::query()->with('app')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        return view('admin.licenses.index', [
            'licenses' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function show(License $license): View
    {
        $license->load(['app', 'edition', 'order', 'devices', 'events' => fn ($q) => $q->latest()->limit(50)]);

        return view('admin.licenses.show', ['license' => $license]);
    }

    public function deactivateDevice(LicenseDevice $device): RedirectResponse
    {
        $this->licenses->deactivateByAdmin($device, 'admin_override');

        AuditLogger::record('license.device_deactivated', $device->license, null, ['device_id' => $device->id, 'platform' => $device->platform], $device->license->customer_email);

        return back()->with('status', 'Device deactivated.');
    }

    public function resetActivations(License $license): RedirectResponse
    {
        $this->licenses->resetActivations($license);

        AuditLogger::record('license.activations_reset', $license, null, null, $license->customer_email);

        return back()->with('status', 'All device activations reset for this license.');
    }

    public function revoke(License $license): RedirectResponse
    {
        $before = ['status' => $license->status];
        $this->licenses->revoke($license);

        AuditLogger::record('license.revoked', $license, $before, ['status' => 'revoked'], $license->customer_email);

        return back()->with('status', 'License revoked.');
    }

    public function restore(License $license): RedirectResponse
    {
        $before = ['status' => $license->status];
        $this->licenses->restore($license);

        AuditLogger::record('license.restored', $license, $before, ['status' => 'active'], $license->customer_email);

        return back()->with('status', 'License restored.');
    }

    public function markRefunded(License $license): RedirectResponse
    {
        $before = ['status' => $license->status];
        $this->licenses->markRefunded($license);

        AuditLogger::record('license.marked_refunded', $license, $before, ['status' => 'refunded'], $license->customer_email);

        return back()->with('status', 'License marked as refunded.');
    }

    public function resendEmail(License $license): RedirectResponse
    {
        try {
            $this->licenses->resendEmail($license);
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Couldn\'t resend the license email: '.$e->getMessage()]);
        }

        AuditLogger::record('license.email_resent', $license, null, null, $license->customer_email);

        return back()->with('status', "License email resent to {$license->customer_email}.");
    }
}
