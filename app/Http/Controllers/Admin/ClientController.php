<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        // Vérifier que l'utilisateur est admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        // Récupérer les clients avec pagination
        $clients = User::where('role', 'client')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiques
        $activeClients = User::where('role', 'client')
            ->where('is_approved', true)
            ->where('subscription_status', 'active')
            ->count();

        $pendingClients = User::where('role', 'client')
            ->where('is_approved', false)
            ->count();

        return view('admin.clients.index', compact('clients', 'activeClients', 'pendingClients'));
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        $client = User::where('role', 'client')->findOrFail($id);
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Mettre à jour un client
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($client->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'is_approved' => 'boolean',
            'subscription_status' => 'nullable|in:active,pending,inactive,suspended',
            'plan' => 'nullable|string|max:50',
            'subscription_ends_at' => 'nullable|date',
        ]);

        // Mettre à jour les informations de base
        $client->name = $validated['name'];
        $client->email = $validated['email'];
        $client->phone = $validated['phone'] ?? null;
        $client->is_approved = $validated['is_approved'] ?? false;

        // Mettre à jour le mot de passe si fourni
        if (!empty($validated['password'])) {
            $client->password = Hash::make($validated['password']);
        }

        // Mettre à jour les informations d'abonnement
        $client->subscription_status = $validated['subscription_status'] ?? null;
        $client->plan = $validated['plan'] ?? null;
        $client->subscription_ends_at = $validated['subscription_ends_at'] ?? null;

        $client->save();

        return redirect()->route('admin.clients')
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Supprimer un client
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        $client = User::where('role', 'client')->findOrFail($id);

        // Empêcher la suppression de soi-même
        if ($client->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $client->delete();

        return redirect()->route('admin.clients')
            ->with('success', 'Client supprimé avec succès.');
    }

    public function approve($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        $client = User::findOrFail($id);
        $client->is_approved = true;
        $client->save();

        return redirect()->route('admin.clients')
            ->with('success', 'Client approuvé avec succès');
    }

    public function activate(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'plan' => 'required|in:basic,normal,elite',
            'months' => 'required|integer|min:1|max:12',
        ]);

        $client = User::findOrFail($id);
        $client->subscription_status = 'active';
        $client->plan = $request->plan;
        $client->subscription_ends_at = now()->addMonths($request->months);
        $client->save();

        return redirect()->route('admin.clients')
            ->with('success', 'Abonnement activé avec succès');
    }
}
