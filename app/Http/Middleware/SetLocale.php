<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['es', 'en', 'ca'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? config('app.locale');

        if (!in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
