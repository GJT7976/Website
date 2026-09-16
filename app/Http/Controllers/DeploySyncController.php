<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

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

        // Artisan::call() throws straight through on a failed migration/
        // seeder — previously uncaught here, which surfaced as an opaque
        // generic 500 (production runs with APP_DEBUG=false) with no way
        // to tell what actually broke without server log access. Caught
        // explicitly so the caller gets the real exception instead, same
        // as MigrateController/SeedController should (see their own
        // follow-up note) — this endpoint is bearer-token-authenticated,
        // not public, so returning the message/class here doesn't leak
        // anything a legitimate caller couldn't already infer by having
        // the token in the first place.
        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'step' => 'migrate',
                'error' => $e::class.': '.$e->getMessage(),
            ], 500);
        }

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'step' => 'seed',
                'migrate' => $migrateOutput,
                'error' => $e::class.': '.$e->getMessage(),
            ], 500);
        }

        AuditLogger::record('database.deploy-sync', label: 'Deploy sync (migrate + seed) triggered via CLI token');

        return response()->json([
            'ok' => true,
            'migrate' => $migrateOutput,
            'seed' => $seedOutput,
        ]);
    }
}
