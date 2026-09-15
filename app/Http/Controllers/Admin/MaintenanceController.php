<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Toggles Laravel's built-in maintenance mode from a button in the admin
 * backend rather than requiring shell access — this host (see
 * HOSTINGER_DEPLOYMENT.md) has no SSH by default, so `php artisan
 * down`/`up` isn't otherwise reachable. Artisan::call() runs the command
 * in-process (no exec()/proc_open() involved), which also matters here:
 * both of those are disabled on this Hostinger plan.
 *
 * bootstrap/app.php excludes 'admin*' from maintenance mode so the owner
 * can always reach this screen to turn it back off again.
 */
class MaintenanceController extends Controller
{
    public function edit(): View
    {
        return view('admin.maintenance.edit', [
            'active' => app()->isDownForMaintenance(),
        ]);
    }

    public function store(): RedirectResponse
    {
        Artisan::call('down', [
            '--render' => 'errors.503',
        ]);

        AuditLogger::record('maintenance.enabled', label: 'Maintenance mode turned on');

        return redirect()->route('admin.maintenance.edit')
            ->with('status', 'Maintenance mode is on — visitors now see the maintenance page. The admin backend stays reachable.');
    }

    public function destroy(): RedirectResponse
    {
        Artisan::call('up');

        AuditLogger::record('maintenance.disabled', label: 'Maintenance mode turned off');

        return redirect()->route('admin.maintenance.edit')
            ->with('status', 'Maintenance mode is off — the site is live again.');
    }
}
