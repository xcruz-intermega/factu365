<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CentralLoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'company' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $tenant = $this->resolveTenant($request->company);

        $authenticated = false;

        $tenant->run(function () use ($request, &$authenticated) {
            $authenticated = Auth::attempt(
                $request->only('email', 'password'),
                $request->boolean('remember')
            );
        });

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect("/{$tenant->slug}/dashboard");
    }

    private function resolveTenant(string $company): Tenant
    {
        $tenant = Tenant::where('slug', $company)->first();

        if ($tenant) {
            return $tenant;
        }

        $tenants = Tenant::where('name', $company)->get();

        if ($tenants->isEmpty()) {
            throw ValidationException::withMessages([
                'company' => trans('auth.company_not_found'),
            ]);
        }

        if ($tenants->count() > 1) {
            throw ValidationException::withMessages([
                'company' => trans('auth.company_multiple'),
            ]);
        }

        return $tenants->first();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}
