<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MT5Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $accounts = MT5Account::where('user_id', $user->id)->get();

        return view('client.accounts.index', [
            'accounts' => $accounts,
            'user' => $user,
        ]);
    }

    public function create()
    {
        return view('client.accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|unique:mt5_accounts',
            'broker' => 'required|string',
            'server' => 'required|string',
            'password' => 'required|string',
            'investor_password' => 'nullable|string',
        ]);

        MT5Account::create([
            'user_id' => auth()->id(),
            'account_number' => $validated['account_number'],
            'broker' => $validated['broker'],
            'server' => $validated['server'],
            'password' => $validated['password'],
            'investor_password' => $validated['investor_password'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('client.accounts')
            ->with('success', 'Compte MT5 ajouté avec succès');
    }

    public function edit(MT5Account $account)
    {
        // Vérifier que l'utilisateur possède ce compte
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé');
        }

        return view('client.accounts.edit', compact('account'));
    }

    public function update(Request $request, MT5Account $account)
    {
        // Vérifier que l'utilisateur possède ce compte
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'broker' => 'required|string',
            'server' => 'required|string',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $account->update($validated);

        return redirect()->route('client.accounts')
            ->with('success', 'Compte mis à jour avec succès');
    }

    public function destroy(MT5Account $account)
    {
        // Vérifier que l'utilisateur possède ce compte
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé');
        }

        $account->delete();

        return redirect()->route('client.accounts')
            ->with('success', 'Compte supprimé avec succès');
    }
}
