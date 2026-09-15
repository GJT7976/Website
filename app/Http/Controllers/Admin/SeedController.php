<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Re-runs the database seeders from a button in the admin backend —
 * needed because this host has no SSH by default (see
 * HOSTINGER_DEPLOYMENT.md), so `php artisan db:seed` isn't otherwise
 * reachable there, and the deploy pipeline's cron script only runs
 * `migrate --force`, never seeds. Artisan::call() runs in-process (no
 * exec()/proc_open(), both disabled on this Hostinger plan), same
 * approach as Admin\MaintenanceController.
 *
 * Every seeder in database/seeders/ is written with updateOrCreate() (or
 * withTrashed()->updateOrCreate() for AppSeeder specifically — see its
 * docblock) precisely so this is safe to run repeatedly: it never
 * duplicates rows, and it never touches orders/users/licenses/entitlements
 * (DatabaseSeeder deliberately creates no admin user — see its docblock).
 * It only ever creates/updates the reference & catalogue data the
 * seeders define (categories, platforms, apps, pages, settings, FAQs,
 * tax rules) — it will NOT pick up an app added by hand through the
 * admin panel rather than through AppSeeder itself.
 */
class SeedController extends Controller
{
    public function edit(): View
    {
        return view('admin.seed.edit');
    }

    public function store(): RedirectResponse
    {
        Artisan::call('db:seed', ['--force' => true]);

        AuditLogger::record('database.seeded', label: 'Database seeders re-run from the admin backend');

        return redirect()->route('admin.seed.edit')
            ->with('status', 'Seed data synced — categories, platforms, apps, pages, settings, FAQs, and tax rules are now up to date with the code.');
    }
}
