<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicenseDevice;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * §10's customer-facing device management page. No customer accounts
 * exist on this site (see MyDownloadsController's doc block), so access
 * is the same email + one-time-code pattern, gated behind a short-lived
 * signed URL rather than My Downloads' permanent one — this page can
 * mutate state (deactivate a device), so it's deliberately less
 * bookmarkable-forever than a read-only download link.
 */
class LicenseManagementController extends Controller
{
    public function __construct(private LicenseService $licenses) {}

    public function requestForm(): View
    {
        return view('license-manage.request');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'license_key' => ['required', 'string', 'max:32'],
        ]);

        $this->licenses->requestManagementCode($data['email'], $data['license_key']);

        return redirect()
            ->route('license.manage.verify-form', ['email' => $data['email']])
            ->with('status', 'If that email address and license key match, we\'ve emailed you a management code.');
    }

    public function verifyForm(Request $request): View
    {
        return view('license-manage.verify', ['email' => $request->query('email', '')]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $url = $this->licenses->verifyManagementCode($data['email'], $data['code']);

        if (! $url) {
            return back()->withErrors(['code' => 'That code is invalid or has expired.'])->withInput();
        }

        return redirect($url);
    }

    public function show(License $license): View
    {
        $devices = $license->devices()->orderByDesc('activated_at')->get()->map(function (LicenseDevice $device) use ($license) {
            return [
                'device' => $device,
                'deactivate_url' => $device->status === 'active'
                    ? URL::temporarySignedRoute('license.manage.deactivate', now()->addMinutes(30), ['license' => $license->id, 'device' => $device->id])
                    : null,
            ];
        });

        return view('license-manage.show', ['license' => $license, 'devices' => $devices]);
    }

    public function deactivate(Request $request, License $license, LicenseDevice $device): RedirectResponse
    {
        abort_unless($device->license_id === $license->id, 404);

        // The `show` route requires a valid signature, so we can't just
        // redirect back to its plain route — mint a fresh short-lived one.
        $showUrl = URL::temporarySignedRoute('license.manage.show', now()->addMinutes(30), ['license' => $license->id]);

        if ($device->status === 'active') {
            $result = $this->licenses->deactivateByCustomer($device, $request->ip());

            if (! $result['ok']) {
                return redirect($showUrl)->withErrors(['device' => $result['message']]);
            }
        }

        return redirect($showUrl)->with('status', 'Device deactivated.');
    }
}
