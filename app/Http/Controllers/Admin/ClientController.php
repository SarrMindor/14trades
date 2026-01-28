<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:admin,client,moderator',
            'is_approved' => 'boolean',
            'is_active' => 'boolean',
            'subscription_status' => 'nullable|in:active,pending,inactive,suspended,expired',
            'plan' => 'nullable|in:starter,basic,normal,pro,elite',
            'subscription_ends_at' => 'nullable|date',
            'force_password_reset' => 'boolean',
        ]);

        // Mise à jour des informations de base
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->role = $validated['role'];
        $user->is_approved = $validated['is_approved'] ?? $user->is_approved;
        $user->is_active = $validated['is_active'] ?? $user->is_active;
        $user->subscription_status = $validated['subscription_status'] ?? $user->subscription_status;
        $user->plan = $validated['plan'] ?? $user->plan;
        $user->subscription_ends_at = $validated['subscription_ends_at'] ?? $user->subscription_ends_at;
        $user->force_password_reset = $validated['force_password_reset'] ?? $user->force_password_reset;

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

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

    /**
     * Approuver un utilisateur
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->is_approved = true;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur approuvé avec succès');
    }

    /**
     * Activer un abonnement
     */
    public function activate(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:basic,normal,elite',
            'months' => 'required|integer|min:1|max:12',
        ]);

        $user = User::findOrFail($id);
        $user->subscription_status = 'active';
        $user->plan = $request->plan;
        $user->subscription_ends_at = now()->addMonths($request->months);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Abonnement activé avec succès');
    }
}
