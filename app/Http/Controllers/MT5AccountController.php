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

        // Vérifier les limites
        $accountsCount = MT5Account::where('user_id', $user->id)->count();
        $maxAccounts = $this->getMaxAccounts($user->plan);

        if ($accountsCount >= $maxAccounts) {
            return redirect()->route('client.accounts.index')
                ->with('error', $this->getLimitMessage($user->plan, $accountsCount));
        }

        // Validation
        $request->validate([
            'account_number' => 'required|numeric|digits_between:5,15',
            'password' => 'required|string|min:6',
            'server' => 'required|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
        ]);

        try {
            // Vérifier si le compte existe déjà
            $existingAccount = MT5Account::where('account_number', $request->account_number)
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($existingAccount) {
                return back()->with('error', 'Ce compte MT5 est déjà associé à un autre utilisateur.');
            }

            // Vérifier les identifiants avec l'API MT5
            $isValid = $this->mt5Service->verifyCredentials(
                $request->account_number,
                $request->password
            );

            if (!$isValid) {
                return back()->with('error', 'Identifiants MT5 invalides. Veuillez vérifier le numéro de compte et le mot de passe.');
            }

            // Obtenir les informations du compte
            $accountInfo = $this->mt5Service->getAccountInfo(
                $request->account_number,
                $request->password
            );

            if (!$accountInfo) {
                return back()->with('error', 'Impossible de récupérer les informations du compte.');
            }

            // Créer le compte
            $account = MT5Account::create([
                'user_id' => $user->id,
                'account_number' => $request->account_number,
                'password' => Crypt::encryptString($request->password),
                'server' => $request->server,
                'balance' => $accountInfo['balance'] ?? 0,
                'equity' => $accountInfo['equity'] ?? 0,
                'margin' => $accountInfo['margin'] ?? 0,
                'free_margin' => $accountInfo['free_margin'] ?? 0,
                'margin_level' => $accountInfo['margin_level'] ?? 0,
                'currency' => $accountInfo['currency'] ?? 'USD',
                'leverage' => $accountInfo['leverage'] ?? 100,
                'name' => $accountInfo['name'] ?? $request->name,
                'email' => $accountInfo['email'] ?? $request->email,
                'company' => $accountInfo['company'] ?? null,
                'status' => $accountInfo['status'] ?? 'active',
                'is_active' => true,
                'last_sync' => now(),
                'meta_data' => json_encode($accountInfo),
            ]);

            // Synchroniser les trades initiaux
            $this->syncAccountTrades($account);

            return redirect()->route('client.accounts.index')
                ->with('success', 'Compte MT5 ajouté et synchronisé avec succès !');

        } catch (\Exception $e) {
            Log::error('Add MT5 Account Error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'ajout du compte: ' . $e->getMessage());
        }
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
