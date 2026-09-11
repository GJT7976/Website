<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Restrict a route to one or more admin roles, e.g.
     * ->middleware('role:owner') for owner-only sections such as
     * Settings and Users.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
