<?php

namespace App\Http\Controllers;

use App\Models\MT5Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MT5Controller extends Controller
{
    private $secretToken = 'Trade_token';

    public function receiveData(Request $request)
    {
        // 🔐 Vérification du token
        if ($request->header('X-WEBHOOK-TOKEN') !== $this->secretToken) {
            Log::warning('❌ Token invalide', [
                'token_reçu' => $request->header('X-WEBHOOK-TOKEN'),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        }

        $data = $request->all();
        Log::info('📥 Webhook MT5 reçu', $data);

        // ✅ Validation des champs requis
        if (!isset($data['login'])) {
            Log::error('❌ Login manquant dans la requête');
            return response()->json([
                'status' => 'error',
                'message' => 'login manquant'
            ], 400);
        }

        if (!isset($data['server'])) {
            Log::error('❌ Server manquant dans la requête');
            return response()->json([
                'status' => 'error',
                'message' => 'server manquant'
            ], 400);
        }

        if (!isset($data['status'])) {
            Log::error('❌ Status manquant dans la requête');
            return response()->json([
                'status' => 'error',
                'message' => 'status manquant'
            ], 400);
        }

        if (!isset($data['hwid'])) {
            Log::error('❌ HWID manquant dans la requête');
            return response()->json([
                'status' => 'error',
                'message' => 'hwid manquant'
            ], 400);
        }

        $login = $data['login'];
        $server = $data['server'];
        $status = $data['status'];
        $hwid = $data['hwid'];
        $ip = $request->ip();

        // 🔍 Vérification si le compte existe dans la base de données
        $account = MT5Account::where('account_number', $login)->first();

        if (!$account) {
            Log::warning('⛔ Compte MT5 non trouvé en base de données', [
                'login' => $login,
                'server' => $server,
                'hwid' => $hwid
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Compte MT5 non autorisé. Veuillez d\'abord enregistrer ce compte.',
                'login' => $login
            ], 403);
        }

        // 🔍 Vérification du serveur
        if ($account->server !== $server) {
            Log::warning('⛔ Serveur MT5 ne correspond pas', [
                'login' => $login,
                'server_reçu' => $server,
                'server_enregistré' => $account->server
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Le serveur ne correspond pas au compte enregistré',
                'server_attendu' => $account->server,
                'server_reçu' => $server
            ], 403);
        }

        // 🔍 Vérification du HWID
        if ($account->hwid === null) {
            // Premier enregistrement du HWID
            $account->hwid = $hwid;
            $account->first_connected_at = now();
            $account->save();
            
            Log::info('🆕 HWID enregistré pour la première fois', [
                'login' => $login,
                'hwid' => $hwid,
                'ip' => $ip
            ]);
        } 
        else if ($account->hwid !== $hwid) {
            // Le HWID ne correspond pas - Machine différente
            Log::error('🚨 ALERTE SÉCURITÉ - HWID différent détecté', [
                'login' => $login,
                'hwid_enregistré' => $account->hwid,
                'hwid_reçu' => $hwid,
                'ip' => $ip,
                'server' => $server
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Ce compte est déjà lié à une autre machine. Contactez l\'administrateur.',
                'hwid_enregistré' => substr($account->hwid, 0, 8) . '...',
                'hwid_actuel' => substr($hwid, 0, 8) . '...'
            ], 403);
        }

        // 🔍 Vérification du statut du compte
        if ($account->status !== 'active') {
            Log::warning('⛔ Compte MT5 désactivé', [
                'login' => $login,
                'status_compte' => $account->status
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Ce compte MT5 est désactivé',
                'account_status' => $account->status
            ], 403);
        }

        // ✅ Tout est OK - Mise à jour du last_sync et des données
        $account->update([
            'last_sync' => now(),
            'last_ip' => $ip,
            'balance' => $data['balance'] ?? $account->balance,
            'equity' => $data['equity'] ?? $account->equity,
        ]);

        Log::info('✅ Compte MT5 synchronisé avec succès', [
            'login' => $login,
            'server' => $server,
            'hwid' => substr($hwid, 0, 8) . '...',
            'user_id' => $account->user_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Compte MT5 synchronisé',
            'account' => [
                'id' => $account->id,
                'account_number' => $account->account_number,
                'server' => $account->server,
                'status' => $account->status,
                'balance' => $account->balance,
                'equity' => $account->equity,
                'hwid_registered' => !empty($account->hwid),
                'last_sync' => $account->last_sync,
            ]
        ], 200);
    }

    /**
     * Enregistrer un nouveau compte MT5 (à appeler depuis votre interface Laravel)
     */
    public function registerAccount(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'account_number' => 'required|string|unique:m_t5_accounts,account_number',
            'broker' => 'required|string',
            'server' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        $account = MT5Account::create($validated);

        Log::info('✅ Nouveau compte MT5 enregistré', [
            'account_number' => $account->account_number,
            'user_id' => $account->user_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Compte MT5 enregistré avec succès',
            'account' => $account
        ], 201);
    }

    /**
     * Activer/Désactiver un compte MT5
     */
    public function toggleStatus($id)
    {
        $account = MT5Account::findOrFail($id);
        
        $newStatus = $account->status === 'active' ? 'inactive' : 'active';
        $account->update(['status' => $newStatus]);

        Log::info('🔄 Status du compte MT5 modifié', [
            'account_number' => $account->account_number,
            'ancien_status' => $account->status === 'active' ? 'inactive' : 'active',
            'nouveau_status' => $newStatus
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Compte {$newStatus}",
            'account' => $account
        ]);
    }

    /**
     * Recevoir les notifications de trades
     */
    public function receiveTrade(Request $request)
    {
        // 🔐 Vérification du token
        if ($request->header('X-WEBHOOK-TOKEN') !== $this->secretToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        }

        $data = $request->all();
        
        // Validation
        if (!isset($data['login']) || !isset($data['ticket'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données manquantes'
            ], 400);
        }

        // Vérifier que le compte existe et est actif
        $account = MT5Account::where('account_number', $data['login'])
                              ->where('status', 'active')
                              ->first();

        if (!$account) {
            Log::warning('⛔ Trade rejeté - Compte non autorisé', [
                'login' => $data['login'],
                'ticket' => $data['ticket']
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Compte MT5 non autorisé ou inactif'
            ], 403);
        }

        // Log du trade
        Log::info('📊 Trade MT5 reçu', [
            'action' => $data['action'] ?? 'unknown',
            'ticket' => $data['ticket'],
            'symbol' => $data['symbol'] ?? 'unknown',
            'price' => $data['price'] ?? 0,
            'lot' => $data['lot'] ?? 0,
            'login' => $data['login'],
            'user_id' => $account->user_id
        ]);

        // Vous pouvez sauvegarder le trade en base de données
        // \App\Models\MT5Trade::create([...]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trade enregistré',
            'ticket' => $data['ticket']
        ], 200);
    }

    /**
     * Réinitialiser le HWID d'un compte (Administrateur uniquement)
     */
    public function resetHwid($id)
    {
        $account = MT5Account::findOrFail($id);
        
        $oldHwid = $account->hwid;
        $account->hwid = null;
        $account->first_connected_at = null;
        $account->save();

        Log::warning('🔄 HWID réinitialisé par administrateur', [
            'account_number' => $account->account_number,
            'ancien_hwid' => $oldHwid,
            'user_id' => $account->user_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'HWID réinitialisé. Le compte pourra se connecter depuis une nouvelle machine.',
            'account' => $account
        ]);
    }

    /**
     * Voir les informations HWID d'un compte
     */
    public function getHwidInfo($id)
    {
        $account = MT5Account::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'account' => [
                'account_number' => $account->account_number,
                'hwid' => $account->hwid,
                'hwid_preview' => $account->hwid ? substr($account->hwid, 0, 8) . '...' : null,
                'first_connected_at' => $account->first_connected_at,
                'last_ip' => $account->last_ip,
                'last_sync' => $account->last_sync,
                'is_hwid_registered' => !empty($account->hwid)
            ]
        ]);
    }
}