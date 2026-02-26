<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminAuthController extends Controller
{
    public function create(Request $request)
    {
        if ($request->session()->get('is_super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $adminEmail = config('admin.email');
        $adminPasswordHash = config('admin.password_hash');

        if (! $adminEmail || ! $adminPasswordHash) {
            return back()->withErrors([
                'email' => trans('admin.not_configured'),
            ]);
        }

        if ($request->email !== $adminEmail || ! Hash::check($request->password, $adminPasswordHash)) {
            return back()->withErrors([
                'email' => trans('admin.invalid_credentials'),
            ]);
        }

        $request->session()->put('is_super_admin', true);
        $request->session()->put('admin_email', $adminEmail);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('is_super_admin');
        $request->session()->forget('admin_email');
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }
}
