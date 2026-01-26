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
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        }

        $data = $request->all();
        Log::info('Webhook MT5 reçu', $data);

        // ✅ Vérification du login
        if (!isset($data['login'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'login manquant'
            ], 400);
        }

        $login = $data['login'];
        $server = $data['server'] ?? null;
        $status = $data['status'] ?? 'active';
        $userId = $data['user_id'] ?? null; // Optionnel : si ton EA peut envoyer l'ID Laravel

        // 🔄 Mise à jour ou création automatique
        $account = MT5Account::updateOrCreate(
            ['account_number' => $login],
            [
                'user_id'    => $userId ?? 1, // par défaut 1 si tu n'as pas d'ID fourni
                'broker'     => 'Exness',     // par défaut
                'server'     => $server,
                'status'     => $status,
                'last_sync'  => now()
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Compte MT5 synchronisé',
            'account' => [
                'id'             => $account->id,
                'account_number' => $account->account_number,
                'server'         => $account->server,
                'status'         => $account->status,
                'last_sync'      => $account->last_sync,
            ]
        ]);
    }
}
