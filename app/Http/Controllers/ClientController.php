<?php

namespace App\Http\Controllers;

use App\Models\LicensedAccount;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    public function index()
    {
        $user = auth()->user();
        $accounts = $user->licensedAccounts()->orderBy('created_at', 'desc')->get();
        return view('client.accounts', compact('user', 'accounts'));
    }


    // Dashboard client
    public function dashboard()
    {
        $user = Auth::user();
        $accounts = $user->licensedAccounts()->with('accessLogs')->get();
        $payments = $user->payments()->orderBy('created_at', 'desc')->take(5)->get();

        // Calculer les jours restants
        $daysRemaining = null;
        $user = Auth::user();
        if ($user->subscription_ends_at) {
            $daysRemaining = now()->diffInDays($user->subscription_ends_at, false);
            if ($daysRemaining < 0) $daysRemaining = 0;
        }

        // Statistiques
        $stats = [
            'active_accounts' => $accounts->where('is_active', true)->count(),
            'total_payments' => $payments->count(),
            'verified_payments' => $payments->where('status', 'verified')->count(),
            'total_accounts' => $accounts->count(),
        ];

        return view('client.dashboard', compact('user', 'accounts', 'payments', 'daysRemaining', 'stats'));
    }
    public function trades()
    {
        $user = auth()->user();

        // Exemple : récupérer les trades associés à l'utilisateur
        // Assure-toi que tu as une relation trades() dans User.php
        $trades = $user->trades()->orderBy('created_at', 'desc')->get();

        return view('client.trades', compact('user', 'trades'));
    }


    // Gestion des comptes MT5
    public function accounts()
    {
        $user = Auth::user();
        $accounts = $user->licensedAccounts()->orderBy('created_at', 'desc')->get();
        $maxAccounts = $this->getMaxAccounts($user->plan);

        return view('client.accounts', compact('accounts', 'maxAccounts', 'user'));
    }

    // Ajouter un compte MT5
    public function addAccount(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string|unique:licensed_accounts,account_id|max:20',
        ], [
            'account_id.unique' => 'Ce numéro de compte MT5 est déjà utilisé.'
        ]);

        $user = Auth::user();
        $accountCount = $user->licensedAccounts()->count();
        $maxAccounts = $this->getMaxAccounts($user->plan);

        if ($accountCount >= $maxAccounts) {
            return back()->withErrors([
                'account_id' => "Vous avez atteint la limite de comptes pour votre plan {$user->plan} (max: {$maxAccounts})."
            ]);
        }

        // Vérifier que l'utilisateur a un abonnement actif
        if (!$user->is_approved || $user->subscription_status !== 'active') {
            return back()->withErrors([
                'account_id' => 'Votre compte n\'est pas encore approuvé ou votre abonnement est inactif.'
            ]);
        }

        LicensedAccount::create([
            'user_id' => $user->id,
            'account_id' => $request->account_id,
            'is_active' => false,
            'api_token' => \Illuminate\Support\Str::random(40)
        ]);

        return back()->with('success', 'Compte MT5 ajouté avec succès ! L\'administrateur l\'activera sous 24h.');
    }

    // Supprimer un compte MT5
    public function deleteAccount($id)
    {
        $account = LicensedAccount::findOrFail($id);

        if ($account->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier si le compte est actif
        if ($account->is_active) {
            return back()->withErrors(['error' => 'Impossible de supprimer un compte actif. Contactez l\'administrateur.']);
        }

        $account->delete();

        return back()->with('success', 'Compte supprimé avec succès.');
    }

    // Page de paiement (redirection vers PaymentController)
    public function payment()
    {
        return redirect()->route('client.payment');
    }

    // Helper: obtenir le nombre max de comptes selon le plan
    private function getMaxAccounts($plan)
    {
        return match($plan) {
            'basic' => 1,
            'normal' => 3,
            'elite' => 999, // Illimité
            default => 0
        };
    }
}
