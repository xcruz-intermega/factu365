<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Users', [
            'users' => User::orderBy('name')->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => $u->created_at->format('d/m/Y'),
            ]),
            'roles' => [
                ['value' => 'viewer', 'label' => __('settings.role_viewer')],
                ['value' => 'accountant', 'label' => __('settings.role_accountant')],
                ['value' => 'admin', 'label' => __('settings.role_admin')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:viewer,accountant,admin'],
        ]);

        if ($validated['role'] === 'admin' && ! $request->user()->isOwner()) {
            abort(403, __('settings.error_only_owner_admin'));
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', __('settings.flash_user_created'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->isOwner() && $user->id !== $request->user()->id) {
            abort(403, __('settings.error_cannot_modify_owner'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:viewer,accountant,admin,owner'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (in_array($validated['role'], ['admin', 'owner']) && ! $request->user()->isOwner()) {
            abort(403, __('settings.error_only_owner_role'));
        }

        // Can't change own role
        if ($user->id === $request->user()->id) {
            unset($validated['role']);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', __('settings.flash_user_updated'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', __('settings.error_cannot_delete_self'));
        }

        if ($user->isOwner()) {
            return back()->with('error', __('settings.error_cannot_delete_owner'));
        }

        $user->delete();

        return back()->with('success', __('settings.flash_user_deleted'));
    }
}
