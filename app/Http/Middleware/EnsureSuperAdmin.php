<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('is_super_admin')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
