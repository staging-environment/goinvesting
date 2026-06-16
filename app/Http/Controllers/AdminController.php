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
            'weekly_spend_limit' => 'nullable|numeric|min:0'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'daily_spend_limit' => $request->input('daily_spend_limit'),
            'weekly_spend_limit' => $request->input('weekly_spend_limit')
        ]);

        return redirect()->back()->with('success', "Límites de gasto para el usuario {$user->name} actualizados.");
    }
}
