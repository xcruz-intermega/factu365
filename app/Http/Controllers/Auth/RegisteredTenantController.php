<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredTenantController extends Controller
{
    private const RESERVED_SLUGS = [
        'register', 'login', 'logout', 'api', 'admin', 'build', 'up',
        'password', 'verify-email', 'confirm-password', 'forgot-password',
        'reset-password',
    ];

    public function create()
    {
        return Inertia::render('Auth/RegisterTenant');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
                function ($attribute, $value, $fail) {
                    if (in_array($value, self::RESERVED_SLUGS, true)) {
                        $fail('Este identificador está reservado.');
                    }
                    $exists = Tenant::where('slug', $value)->exists();
                    if ($exists) {
                        $fail('Este identificador ya está en uso.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'slug.regex' => 'El identificador solo puede contener letras minúsculas, números y guiones.',
        ]);

        $freePlan = Plan::where('slug', 'free')->first();

        $tenant = Tenant::create([
            'id' => Str::uuid()->toString(),
            'slug' => $request->slug,
            'name' => $request->company_name,
            'email' => $request->email,
            'plan_id' => $freePlan?->id,
            'trial_ends_at' => now()->addDays(14),
        ]);

        $tenant->domains()->create([
            'domain' => $request->slug,
        ]);

        // Create owner user inside tenant context
        $tenant->run(function () use ($request) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
            ]);
        });

        return Inertia::location("/{$request->slug}/login");
    }
}
