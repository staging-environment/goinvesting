<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function dashboard()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $users = User::all();
        return view('admin.dashboard', compact('users'));
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|in:admin,investor'
        ]);

        $user = User::findOrFail($id);
        
        // Prevent removing own admin privileges
        if ($user->id === auth()->id() && $request->input('role') !== 'admin') {
            return redirect()->back()->withErrors(['error' => 'No puedes revocarte los permisos de administrador a ti mismo.']);
        }

        $user->update([
            'role' => $request->input('role')
        ]);

        return redirect()->back()->with('success', "Rol del usuario {$user->name} actualizado a " . ($user->role === 'admin' ? 'Administrador' : 'Inversor') . ".");
    }

    /**
     * Update user spending limits.
     */
    public function updateLimits(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'daily_spend_limit' => 'nullable|numeric|min:0',
            'weekly_spend_limit' => 'nullable|numeric|min:0',
            'monthly_spend_limit' => 'nullable|numeric|min:0'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'daily_spend_limit' => $request->input('daily_spend_limit'),
            'weekly_spend_limit' => $request->input('weekly_spend_limit'),
            'monthly_spend_limit' => $request->input('monthly_spend_limit')
        ]);

        return redirect()->back()->with('success', "Límites de gasto para el usuario {$user->name} actualizados.");
    }

    /**
     * Show create user form.
     */
    public function createUserForm()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return view('admin.create-user');
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,investor',
            'daily_spend_limit' => 'nullable|numeric|min:0',
            'weekly_spend_limit' => 'nullable|numeric|min:0',
            'monthly_spend_limit' => 'nullable|numeric|min:0',
        ]);

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role' => $request->input('role'),
            'daily_spend_limit' => $request->input('daily_spend_limit'),
            'weekly_spend_limit' => $request->input('weekly_spend_limit'),
            'monthly_spend_limit' => $request->input('monthly_spend_limit'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show edit user form.
     */
    public function editUserForm($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $user = User::findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,investor',
            'daily_spend_limit' => 'nullable|numeric|min:0',
            'weekly_spend_limit' => 'nullable|numeric|min:0',
            'monthly_spend_limit' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'daily_spend_limit' => $request->input('daily_spend_limit'),
            'weekly_spend_limit' => $request->input('weekly_spend_limit'),
            'monthly_spend_limit' => $request->input('monthly_spend_limit'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->input('password'));
        }

        // Prevent removing own admin privileges
        if ($user->id === auth()->id() && $request->input('role') !== 'admin') {
            return redirect()->back()->withErrors(['error' => 'No puedes revocarte los permisos de administrador a ti mismo.']);
        }

        $user->update($updateData);

        return redirect()->route('admin.dashboard')->with('success', "Usuario {$user->name} actualizado correctamente.");
    }
}

