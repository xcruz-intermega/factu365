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
                ['value' => 'viewer', 'label' => 'Visualizador'],
                ['value' => 'accountant', 'label' => 'Contable'],
                ['value' => 'admin', 'label' => 'Administrador'],
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
            abort(403, 'Solo el propietario puede crear administradores.');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->isOwner() && $user->id !== $request->user()->id) {
            abort(403, 'No se puede modificar al propietario.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:viewer,accountant,admin,owner'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (in_array($validated['role'], ['admin', 'owner']) && ! $request->user()->isOwner()) {
            abort(403, 'Solo el propietario puede asignar este rol.');
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

        return back()->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($user->isOwner()) {
            return back()->with('error', 'No se puede eliminar al propietario.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
