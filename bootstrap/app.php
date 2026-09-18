<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Only the admin backend has authentication in Phase 1 (no public
        // customer accounts yet), so guests/authenticated users are always
        // routed relative to /admin.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        // Stripe signs its own webhook payloads (verified in
        // StripeWebhookController) — it can't send a Laravel CSRF token.
        // deploy-sync is likewise a non-browser caller (the CLI) —
        // authenticated by its own bearer token instead; see
        // DeploySyncController's docblock.
        $middleware->validateCsrfTokens(except: ['stripe/webhook', 'deploy-sync', 'release-sync/*']);

        // Keep the admin backend reachable while the public site is in
        // maintenance mode, so the owner can turn it back off from the
        // Maintenance Mode screen instead of being locked out with
        // everyone else (see Admin\MaintenanceController).
        $middleware->preventRequestsDuringMaintenance(except: ['admin*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Some deployments of this project (a leftover from an earlier, manual
// Hostinger setup — see HOSTINGER_DEPLOYMENT.md) clone into a project
// folder (e.g. .../domains/niagaraindieapps.com/niagara_app) sitting
// *next to* the domain's public_html, with only this project's public/
// contents relocated into that public_html sibling and this project's
// own public/ left empty. Laravel's default public_path() still
// resolves to that now-empty public/, so anything reading from disk
// there (the Vite manifest, the storage symlink) 404s/500s even though
// the real files exist one level up in public_html.
//
// Guard on basename(): when this project's own root already *is*
// public_html (the standard "git clone straight into public_html"
// layout Hostinger's Git deploy tool actually uses), the sibling lookup
// below would otherwise resolve to public_html itself and wrongly
// override public_path() to the project root instead of its real
// public/ subfolder — this must stay a no-op there, and everywhere
// else (local dev, a host where public/ is the real doc root) that has
// no public_html sibling at all.
$publicHtml = dirname($app->basePath()).'/public_html';

if (basename($app->basePath()) !== 'public_html' && is_dir($publicHtml)) {
    $app->usePublicPath($publicHtml);
}

return $app;
