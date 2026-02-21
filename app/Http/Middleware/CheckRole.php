<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:admin') or ->middleware('role:accountant')
     * Checks that the user's role meets the minimum required level.
     *
     * Hierarchy: owner > admin > accountant > viewer
     */
    public function handle(Request $request, Closure $next, string $minimumRole): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $hierarchy = [
            'viewer' => 0,
            'accountant' => 1,
            'admin' => 2,
            'owner' => 3,
        ];

        $userLevel = $hierarchy[$user->role] ?? -1;
        $requiredLevel = $hierarchy[$minimumRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
