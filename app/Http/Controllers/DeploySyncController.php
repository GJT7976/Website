<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * CLI-triggerable counterpart to Admin\SeedController and
 * Admin\MigrateController — runs `migrate --force` then `db:seed --force`
 * over HTTP, authenticated by a long random bearer token instead of an
 * admin browser session. Exists so the UPDATE WEBSITE workflow can be
 * fully hands-off end to end (push → Hostinger's Auto-deployment pulls it
 * → this endpoint syncs schema/data) without SSH, which this Hostinger
 * plan doesn't have by default (see DEPLOYMENT.md).
 *
 * Deliberately outside the admin/ route group and its `auth`/`role:owner`
 * middleware — a CLI call has no browser session to authenticate with.
 * Security instead rests on: the token is 256 bits of CSPRNG output (not
 * a human-memorable password), compared with hash_equals() to avoid a
 * timing side-channel, required non-empty (config('services.deploy_sync.token')
 * unset means this 404s rather than accepting a blank token), and the
 * route is throttled. migrate/seed are themselves safe to call this way:
 * every seeder is updateOrCreate()-based (idempotent, no data loss) and
 * migrate only adds schema the code already expects — see
 * MigrateController's docblock for why that still isn't destructive.
 */
class DeploySyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = config('services.deploy_sync.token');
        abort_if(blank($expected), 404);

        $provided = (string) $request->bearerToken();
        abort_unless(hash_equals($expected, $provided), 403);

        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = trim(Artisan::output());

        AuditLogger::record('database.deploy-sync', label: 'Deploy sync (migrate + seed) triggered via CLI token');

        return response()->json([
            'ok' => true,
            'migrate' => $migrateOutput,
            'seed' => $seedOutput,
        ]);
    }
}
