<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        $activeUsers = User::where('is_approved', true)
            ->where('subscription_status', 'active')
            ->count();

        $inactiveUsers = User::where('is_approved', false)
            ->orWhere('subscription_status', '!=', 'active')
            ->count();

        return view('admin.users.index', compact('users', 'activeUsers', 'inactiveUsers'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,client,moderator',
            'is_approved' => 'boolean',
            'is_active' => 'boolean',
            'subscription_status' => 'nullable|in:active,pending,inactive,suspended,expired',
            'plan' => 'nullable|in:starter,pro,elite',
            'subscription_ends_at' => 'nullable|date',
            'force_password_reset' => 'boolean',
        ]);

        // Mise à jour des informations de base
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'role' => $validated['role'],
            'is_approved' => $validated['is_approved'] ?? $user->is_approved,
            'is_active' => $validated['is_active'] ?? $user->is_active,
            'subscription_status' => $validated['subscription_status'] ?? $user->subscription_status,
            'plan' => $validated['plan'] ?? $user->plan,
            'subscription_ends_at' => $validated['subscription_ends_at'] ?? $user->subscription_ends_at,
        ]);

        // Gestion du mot de passe
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|min:8|confirmed',
            ]);

            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Force password reset
        if ($request->has('force_password_reset')) {
            $user->force_password_reset = true;
            $user->save();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Vérifier que l'utilisateur n'est pas en train de se supprimer lui-même
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }
}
