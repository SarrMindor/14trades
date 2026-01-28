<?php

namespace App\Http\Controllers;

use App\Models\MT5Account;
use App\Services\MT5ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class MT5AccountController extends Controller
{
    protected $mt5Service;

    public function __construct(MT5ApiService $mt5Service)
    {
        $this->mt5Service = $mt5Service;
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des comptes
     */
    public function index()
    {
        $user = Auth::user();
        $accounts = MT5Account::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $apiConnected = $this->mt5Service->testConnection();

        return view('client.accounts.index', compact('accounts', 'user', 'apiConnected'));
    }

    /**
     * Afficher le formulaire d'ajout
     */
    public function create()
    {
        $user = Auth::user();

        // Vérifier les limites du plan
        $accountsCount = MT5Account::where('user_id', $user->id)->count();
        $maxAccounts = $this->getMaxAccounts($user->plan);

        if ($accountsCount >= $maxAccounts) {
            return redirect()->route('client.accounts.index')
                ->with('error', $this->getLimitMessage($user->plan, $accountsCount));
        }

        return view('client.accounts.create', compact('user'));
    }

    /**
     * Ajouter un nouveau compte
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validation SEULEMENT pour les 2 champs du formulaire
        $validated = $request->validate([
            'account_number' => 'required|numeric|digits_between:5,9|unique:mt5_accounts,account_number',
            'email' => 'required|email|max:255',
        ]);

        // Vérifier les limites
        $accountsCount = MT5Account::where('user_id', $user->id)->count();
        $maxAccounts = $this->getMaxAccounts($user->plan);

        if ($accountsCount >= $maxAccounts) {
            return redirect()->route('client.accounts.create')
                ->with('error', $this->getLimitMessage($user->plan, $accountsCount));
        }

        // Créer le compte sans vérification
        MT5Account::create([
            'user_id' => $user->id,
            'account_number' => $validated['account_number'],
            'email' => $validated['email'],
            'status' => 'pending',
            'is_active' => false,
            'notes' => "Demande soumise le " . now()->format('d/m/Y H:i:s'),
        ]);

        return redirect()->route('client.accounts.index')
            ->with('success', ' Demande envoyée ! Notre équipe vous contactera à ' . $validated['email']);
    }

    /**
     * Synchroniser un compte spécifique
     */
    public function sync($id)
    {
        $account = MT5Account::findOrFail($id);

        // Vérifier les permissions
        if ($account->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            // Obtenir le mot de passe décrypté
            $password = Crypt::decryptString($account->password);

            // Synchroniser avec l'API MT5
            $syncData = $this->mt5Service->syncAccount($account->account_number, $password);

            // Mettre à jour le compte
            $account->update([
                'balance' => $syncData['account_info']['balance'] ?? $account->balance,
                'equity' => $syncData['account_info']['equity'] ?? $account->equity,
                'margin' => $syncData['account_info']['margin'] ?? $account->margin,
                'free_margin' => $syncData['account_info']['free_margin'] ?? $account->free_margin,
                'margin_level' => $syncData['account_info']['margin_level'] ?? $account->margin_level,
                'status' => $syncData['account_info']['status'] ?? $account->status,
                'last_sync' => now(),
                'sync_error' => null,
                'meta_data' => json_encode($syncData['account_info']),
            ]);

            // Synchroniser les trades
            $this->syncAccountTrades($account, $syncData['recent_trades']);

            return redirect()->route('client.accounts.index')
                ->with('success', 'Compte synchronisé avec succès !');

        } catch (\Exception $e) {
            Log::error('Sync MT5 Account Error: ' . $e->getMessage());

            $account->update([
                'sync_error' => $e->getMessage(),
                'last_sync' => now(),
            ]);

            return redirect()->route('client.accounts.index')
                ->with('error', 'Erreur de synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser tous les comptes de l'utilisateur
     */
    /**
     * Afficher les détails d'un compte spécifique
     */
    /**
     * Afficher les détails d'un compte MT5
     */
    public function show($id)
    {
        $user = Auth::user();

        // Récupérer le compte avec l'utilisateur associé
        $account = MT5Account::with('user')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Calculer les statistiques basiques
        $stats = [
            'margin_level' => $account->margin > 0 ? ($account->equity / $account->margin) * 100 : 0,
            'profit_loss' => $account->equity - $account->balance,
            'profit_percentage' => $account->balance > 0 ? (($account->equity - $account->balance) / $account->balance) * 100 : 0,
            'free_margin_percentage' => $account->margin > 0 ? ($account->free_margin / $account->margin) * 100 : 0,
        ];

        // Simulation d'historique (à remplacer par vos données réelles)
        $history = $this->generateMockHistory($account);

        return view('client.accounts.show', compact('account', 'stats', 'history'));
    }

    /**
     * Générer des données d'historique simulées
     */
    private function generateMockHistory($account)
    {
        $history = [];
        $startBalance = $account->balance;
        $currentBalance = $startBalance;

        // Générer 30 jours d'historique
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyChange = rand(-50, 100); // Variation journalière simulée

            $history[] = [
                'date' => $date->format('Y-m-d'),
                'balance' => $currentBalance + $dailyChange,
                'change' => $dailyChange,
                'change_percent' => $currentBalance > 0 ? ($dailyChange / $currentBalance) * 100 : 0,
            ];

            $currentBalance += $dailyChange;
        }

        return $history;
    }

    /**
     * Obtenir l'historique des performances du compte
     */
    private function getPerformanceHistory($account)
    {
        // Simulation d'historique - À remplacer par vos données réelles
        $history = [];
        $balance = $account->balance;

        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyChange = rand(-100, 150); // Simulation

            $history[] = [
                'date' => $date->format('Y-m-d'),
                'balance' => $balance + $dailyChange,
                'change' => $dailyChange,
                'change_percent' => $balance > 0 ? ($dailyChange / $balance) * 100 : 0,
            ];

            $balance += $dailyChange;
        }

        return $history;
    }
    public function syncAll()
    {
        $user = Auth::user();
        $accounts = MT5Account::where('user_id', $user->id)->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($accounts as $account) {
            try {
                // Obtenir le mot de passe décrypté
                $password = Crypt::decryptString($account->password);

                // Synchroniser avec l'API MT5
                $syncData = $this->mt5Service->syncAccount($account->account_number, $password);

                // Mettre à jour le compte
                $account->update([
                    'balance' => $syncData['account_info']['balance'] ?? $account->balance,
                    'equity' => $syncData['account_info']['equity'] ?? $account->equity,
                    'margin' => $syncData['account_info']['margin'] ?? $account->margin,
                    'free_margin' => $syncData['account_info']['free_margin'] ?? $account->free_margin,
                    'margin_level' => $syncData['account_info']['margin_level'] ?? $account->margin_level,
                    'status' => $syncData['account_info']['status'] ?? $account->status,
                    'last_sync' => now(),
                    'sync_error' => null,
                    'meta_data' => json_encode($syncData['account_info']),
                ]);

                // Synchroniser les trades
                $this->syncAccountTrades($account, $syncData['recent_trades']);

                $successCount++;

            } catch (\Exception $e) {
                Log::error('Sync MT5 Account Error (ID: ' . $account->id . '): ' . $e->getMessage());

                $account->update([
                    'sync_error' => $e->getMessage(),
                    'last_sync' => now(),
                ]);

                $errorCount++;
            }
        }

        $message = "Synchronisation terminée: {$successCount} compte(s) mis à jour";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} erreur(s)";
        }

        return redirect()->route('client.accounts.index')
            ->with($errorCount > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Supprimer un compte
     */
    public function destroy($id)
    {
        $account = MT5Account::findOrFail($id);

        // Vérifier les permissions
        if ($account->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        $account->delete();

        return redirect()->route('client.accounts.index')
            ->with('success', 'Compte supprimé avec succès.');
    }

    /**
     * Obtenir le nombre maximum de comptes selon le plan
     */
    private function getMaxAccounts($plan)
    {
        return match($plan) {
            'basic' => 1,
            'normal' => 3,
            'elite' => PHP_INT_MAX,
            default => 1,
        };
    }

    /**
     * Obtenir le message de limite
     */
    private function getLimitMessage($plan, $currentCount)
    {
        $max = $this->getMaxAccounts($plan);
        return "Vous avez atteint la limite de {$currentCount}/{$max} compte(s) pour votre plan " . strtoupper($plan) . ". Veuillez mettre à niveau votre plan pour ajouter plus de comptes.";
    }

    /**
     * Synchroniser les trades d'un compte
     */
    private function syncAccountTrades($account, $trades = null)
    {
        if (!$trades) {
            try {
                $password = Crypt::decryptString($account->password);
                $trades = $this->mt5Service->getAccountTrades($account->account_number, $password);
            } catch (\Exception $e) {
                Log::error('Get Trades Error: ' . $e->getMessage());
                return;
            }
        }

        // Ici vous pouvez enregistrer les trades dans une table séparée
        // Exemple: MT5Trade::create([...]);
    }
}
