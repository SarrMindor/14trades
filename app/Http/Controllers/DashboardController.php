<?php

namespace App\Http\Controllers;

use App\Services\MT5Service;
use App\Models\User;
use App\Models\MT5Account;
use App\Models\Trade;
use App\Models\Payment;
use App\Models\Alert;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    public function index()
    {
        $user = auth()->user();

        // Si admin, retourner le dashboard admin
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        // Dashboard client avec données réelles
        return $this->clientDashboard($user);
    }

    private function clientDashboard($user)
    {
        // Synchroniser les comptes MT5
        $this->mt5Service->syncUserAccounts($user->id);

        // Récupérer les comptes de l'utilisateur
        $accounts = MT5Account::where('user_id', $user->id)->get();

        // Calculer les statistiques réelles
        $stats = $this->calculateRealStats($user->id, $accounts);

        // Calculer les jours restants
        $daysRemaining = null;
        if ($user->subscription_ends_at) {
            $endDate = \Carbon\Carbon::parse($user->subscription_ends_at);
            $today = \Carbon\Carbon::now();
            $daysRemaining = $today->diffInDays($endDate, false);
        }

        // Récupérer les alertes non lues
        $unreadAlerts = Alert::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('client.dashboard', [
            'user' => $user,
            'accounts' => $accounts,
            'stats' => $stats,
            'daysRemaining' => $daysRemaining,
            'unreadAlerts' => $unreadAlerts,
            'subscriptionStatus' => $user->subscription_status,
            'plan' => $user->plan,
            'subscriptionEndsAt' => $user->subscription_ends_at,
            'isApproved' => $user->is_approved,
        ]);
    }

    private function calculateRealStats($userId, $accounts)
    {
        // Compter les comptes actifs
        $activeAccounts = $accounts->where('status', 'active')->count();

        // Récupérer les trades réels
        $trades = Trade::where('user_id', $userId)->get();
        $totalTrades = $trades->count();

        // Calculer le profit/perte total
        $profitLoss = $trades->sum('profit');

        // Calculer le taux de succès
        $successRate = 0;
        if ($totalTrades > 0) {
            $profitableTrades = $trades->where('result', 'profit')->count();
            $successRate = round(($profitableTrades / $totalTrades) * 100, 1);
        }

        // Récupérer les paiements vérifiés
        $verifiedPayments = Payment::where('user_id', $userId)
            ->where('status', 'verified')
            ->count();

        $totalPayments = Payment::where('user_id', $userId)->count();

        return [
            'active_accounts' => $activeAccounts,
            'total_trades' => $totalTrades,
            'profit_loss' => $profitLoss,
            'success_rate' => $successRate,
            'verified_payments' => $verifiedPayments,
            'total_payments' => $totalPayments,
            'total_balance' => $accounts->sum('balance'),
            'total_equity' => $accounts->sum('equity'),
        ];
    }

    private function adminDashboard()
    {
        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'pending_approvals' => User::where('is_approved', false)->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'total_earnings' => Payment::where('status', 'verified')->sum('amount'),
            'active_today' => User::whereDate('last_login_at', today())->count(),
            'total_accounts' => MT5Account::count(),
        ];

        $recentClients = User::where('role', 'client')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentPayments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $pendingAccounts = MT5Account::where('status', 'pending')
            ->with('user')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentClients', 'recentPayments', 'pendingAccounts'));
    }

    /**
     * Récupérer les trades d'un compte via AJAX
     */
    public function getAccountTrades(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
        ]);

        $trades = $this->mt5Service->getAccountTrades($request->account_number);

        return response()->json($trades);
    }

    /**
     * Synchroniser un compte MT5
     */
    public function syncAccount(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:mt5_accounts,id',
        ]);

        $account = MT5Account::find($request->account_id);

        // Vérifier que l'utilisateur possède ce compte
        if ($account->user_id !== auth()->id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $result = $this->mt5Service->getAccountBalance($account->account_number);

        if (!isset($result['error'])) {
            $account->update([
                'balance' => $result['balance'] ?? $account->balance,
                'equity' => $result['equity'] ?? $account->equity,
                'margin' => $result['margin'] ?? $account->margin,
                'free_margin' => $result['free_margin'] ?? $account->free_margin,
                'last_sync' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte synchronisé avec succès',
                'data' => $account
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => $result['message'] ?? 'Erreur de synchronisation'
        ], 400);
    }
}
