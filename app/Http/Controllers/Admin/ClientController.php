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
