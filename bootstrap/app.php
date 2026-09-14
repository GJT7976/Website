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
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Hostinger's Git-based deploy for this project clones into a project
// folder (e.g. .../domains/niagaraindieapps.com/niagara_app) and then
// physically relocates the contents of this project's public/ folder
// (including the built Vite assets and the storage:link symlink) into
// the domain's public_html sibling folder on every deploy, leaving this
// project's own public/ empty. Laravel's default public_path() still
// resolves to the now-empty public/ folder, so anything that reads from
// disk there (the Vite manifest, the storage symlink) 404s/500s even
// though the real files exist one level up in public_html. Point
// public_path() at that sibling public_html when it exists; this never
// triggers locally or on a host where public/ is the real doc root.
$publicHtml = dirname($app->basePath()).'/public_html';

if (is_dir($publicHtml)) {
    $app->usePublicPath($publicHtml);
}

return $app;
