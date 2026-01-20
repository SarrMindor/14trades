<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\LicensedAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientApprovedMail;
use App\Mail\SubscriptionActivatedMail;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // Dashboard admin
    public function dashboard()
    {
        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'pending_approvals' => User::where('role', 'client')->where('is_approved', false)->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'active_subscriptions' => User::where('subscription_status', 'active')
                ->where('subscription_ends_at', '>', now())
                ->count(),
            'total_earnings' => Payment::where('status', 'verified')->sum('amount'),
            'active_accounts' => LicensedAccount::where('is_active', true)->count(),
            'total_payments' => Payment::count(),
        ];

        // Derniers clients inscrits
        $recentClients = User::where('role', 'client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Derniers paiements
        $recentPayments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Comptes en attente d'activation
        $pendingAccounts = LicensedAccount::where('is_active', false)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentClients', 'recentPayments', 'pendingAccounts'));
    }

    // Liste des clients
    public function clients()
    {
        $clients = User::where('role', 'client')
            ->with(['licensedAccounts', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.clients', compact('clients'));
    }

    // Approuver un client
    public function approveClient($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'client') {
            return back()->withErrors(['error' => 'Seuls les clients peuvent être approuvés.']);
        }

        $user->update(['is_approved' => true]);

        // Envoyer un email de confirmation
        try {
            Mail::to($user->email)->send(new ClientApprovedMail($user));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email: ' . $e->getMessage());
        }

        return back()->with('success', "Client {$user->name} approuvé avec succès !");
    }

    // Activer un abonnement
    public function activateSubscription(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:basic,normal,elite',
            'months' => 'required|integer|min:1|max:12'
        ]);

        $user = User::findOrFail($id);

        if ($user->role !== 'client') {
            return back()->withErrors(['error' => 'Seuls les clients peuvent avoir un abonnement.']);
        }

        $user->update([
            'plan' => $request->plan,
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addMonths($request->months),
            'is_approved' => true // Auto-approve lors de l'activation
        ]);

        // Envoyer un email d'activation
        try {
            Mail::to($user->email)->send(new SubscriptionActivatedMail($user));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email: ' . $e->getMessage());
        }

        return back()->with('success', "Abonnement {$request->plan} activé pour {$request->months} mois !");
    }

    // Liste des paiements
    public function payments()
    {
        $payments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.payments', compact('payments'));
    }

    // Vérifier un paiement
    public function verifyPayment($id)
    {
        $payment = Payment::with('user')->findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Ce paiement a déjà été traité.']);
        }

        // Mettre à jour le statut du paiement
        $payment->update(['status' => 'verified']);

        // Activer l'abonnement de l'utilisateur
        $user = $payment->user;
        $plan = $this->getPlanFromAmount($payment->amount);

        $user->update([
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'plan' => $plan,
            'is_approved' => true
        ]);

        // Envoyer un email de confirmation
        try {
            Mail::to($user->email)->send(new SubscriptionActivatedMail($user));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email: ' . $e->getMessage());
        }

        return back()->with('success', "Paiement vérifié et abonnement {$plan} activé pour {$user->name} !");
    }

    // Rejeter un paiement
    public function rejectPayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Ce paiement a déjà été traité.']);
        }

        $payment->update(['status' => 'rejected']);

        return back()->with('success', 'Paiement rejeté.');
    }

    // Helper: déterminer le plan selon le montant
    private function getPlanFromAmount($amount)
    {
        return match($amount) {
            49 => 'basic',
            99 => 'normal',
            199 => 'elite',
            default => 'basic'
        };
    }

    // Méthodes supplémentaires utiles
    public function getClientDetail($id)
    {
        $client = User::with(['licensedAccounts', 'payments'])->findOrFail($id);
        return view('admin.client-detail', compact('client'));
    }

    public function activateAccount($accountId)
    {
        $account = LicensedAccount::findOrFail($accountId);
        $account->update(['is_active' => true]);

        return back()->with('success', "Compte MT5 {$account->account_id} activé.");
    }

    public function deactivateAccount($accountId)
    {
        $account = LicensedAccount::findOrFail($accountId);
        $account->update(['is_active' => false]);

        return back()->with('success', "Compte MT5 {$account->account_id} désactivé.");
    }

    public function resetHwid($accountId)
    {
        $account = LicensedAccount::findOrFail($accountId);
        $account->update(['hwid' => null]);

        return back()->with('success', "HWID réinitialisé pour le compte {$account->account_id}.");
    }
}
