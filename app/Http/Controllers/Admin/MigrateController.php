<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Runs `php artisan migrate --force` from a button in the admin backend —
 * closes the gap DEPLOYMENT.md flagged 2026-09-15: this host has no SSH by
 * default (see HOSTINGER_DEPLOYMENT.md), and no cron job or Git-deploy
 * build hook actually running migrations was found during that audit
 * (contrast a comment in SeedController claiming one exists — unverified
 * and contradicted by HOSTINGER_DEPLOYMENT.md's own "nothing needs a
 * scheduled job yet" — treat that comment as stale, not a real mechanism,
 * until someone actually confirms it in hPanel). Same Artisan::call()
 * in-process approach as MaintenanceController/SeedController — no
 * exec()/proc_open() involved, both disabled on this Hostinger plan.
 *
 * Shows pending migrations before running, using Laravel's own
 * `migrate:status` output rather than a hand-rolled diff, so what's about
 * to run is visible before committing to it — a schema change is harder
 * to undo than a reseed.
 */
class MigrateController extends Controller
{
    public function edit(): View
    {
        return view('admin.migrate.edit', [
            'pending' => $this->pendingMigrations(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $before = $this->pendingMigrations();

        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        AuditLogger::record('database.migrated', label: 'Database migrations run from the admin backend', after: [
            'migrations_run' => $before,
        ]);

        return redirect()->route('admin.migrate.edit')
            ->with('status', $before === []
                ? 'No pending migrations — database schema was already up to date.'
                : 'Migrations run: '.implode(', ', $before))
            ->with('migrateOutput', $output);
    }

    /**
     * @return list<string>
     */
    private function pendingMigrations(): array
    {
        if (! Schema::hasTable('migrations')) {
            return [];
        }

        Artisan::call('migrate:status', ['--pending' => true]);

        // Only real migration filename lines (YYYY_MM_DD_HHMMSS_name —
        // Laravel's own naming convention), not the table header row or
        // any INFO/blank line the command also prints.
        return collect(explode("\n", Artisan::output()))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => (bool) preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $line))
            ->values()
            ->all();
    }
}
