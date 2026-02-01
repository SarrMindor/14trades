<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MT5Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MT5Controller extends Controller
{
    private string $secretToken;
    private int $heartbeatTimeout;

    public function __construct()
    {
        $this->secretToken = config('services.mt5.webhook_token');
        $this->heartbeatTimeout = (int) config('services.mt5.heartbeat_timeout', 120);
    }

    /**
     * 🔐 ENDPOINT 1 : Connexion initiale (OnInit EA)
     */
    public function connect(Request $request)
    {
        if (!$this->validateToken($request)) {
            return $this->errorResponse('Token invalide', 401);
        }

        $validated = $request->validate([
            'login'  => 'required|numeric',
            'server' => 'required|string',
            'hwid'   => 'required|string|size:64',
        ]);

        try {
            $mt5Account = MT5Account::where('account_number', $validated['login'])
                ->where('server', $validated['server'])
                ->first();

            if (!$mt5Account) {
                Log::warning('Compte MT5 introuvable', $validated);
                return $this->errorResponse('Compte non enregistré', 404);
            }

            if (!$this->validateHWID($mt5Account, $validated['hwid'])) {
                Log::critical('HWID invalide', [
                    'login' => $validated['login'],
                    'hwid_fourni' => $validated['hwid'],
                    'hwid_attendu' => $mt5Account->hwid,
                ]);
                return $this->errorResponse('Machine non autorisée', 403);
            }

            $user = User::find($mt5Account->user_id);
            if (!$user || $user->status !== 'active') {
                return $this->errorResponse('Compte utilisateur désactivé', 403);
            }

            if ($mt5Account->status !== 'active') {
                return $this->errorResponse('Compte MT5 désactivé', 403);
            }

            $mt5Account->update([
                'is_connected'   => true,
                'last_heartbeat' => now(),
                'last_sync'      => now(),
            ]);

            Log::info('EA connecté', [
                'user_id' => $user->id,
                'login'   => $validated['login'],
            ]);

            return response()->json([
                'status'     => 'success',
                'authorized' => true,
                'message'    => 'Connexion autorisée',
                'data'       => [
                    'account_id'        => $mt5Account->id,
                    'user_name'         => $user->name,
                    'heartbeat_interval'=> 60,
                    'heartbeat_timeout' => $this->heartbeatTimeout,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur connexion EA', [
                'exception' => $e->getMessage(),
            ]);
            return $this->errorResponse('Erreur serveur', 500);
        }
    }

    /**
     * 💓 ENDPOINT 2 : Heartbeat
     */
    public function heartbeat(Request $request)
    {
        if (!$this->validateToken($request)) {
            return $this->errorResponse('Token invalide', 401);
        }

        $validated = $request->validate([
            'login'  => 'required|numeric',
            'server' => 'required|string',
            'hwid'   => 'required|string|size:64',
        ]);

        $mt5Account = MT5Account::where('account_number', $validated['login'])
            ->where('server', $validated['server'])
            ->first();

        if (!$mt5Account || $mt5Account->hwid !== $validated['hwid']) {
            Log::warning('Heartbeat invalide', $validated);
            return $this->errorResponse('Session invalide', 404);
        }

        $user = User::find($mt5Account->user_id);
        if (
            !$user ||
            $user->status !== 'active' ||
            $mt5Account->status !== 'active'
        ) {
            $mt5Account->update(['is_connected' => false]);
            return $this->errorResponse('Compte désactivé - Arrêt du trading', 403);
        }

        $mt5Account->update([
            'last_heartbeat' => now(),
            'is_connected'   => true,
        ]);

        return response()->json([
            'status'           => 'success',
            'keep_alive'       => true,
            'heartbeat_timeout'=> $this->heartbeatTimeout,
        ]);
    }

    /**
     * 🔌 ENDPOINT 3 : Déconnexion propre
     */
    public function disconnect(Request $request)
    {
        if (!$this->validateToken($request)) {
            return $this->errorResponse('Token invalide', 401);
        }

        $validated = $request->validate([
            'login'  => 'required|numeric',
            'server' => 'required|string',
        ]);

        MT5Account::where('account_number', $validated['login'])
            ->where('server', $validated['server'])
            ->update([
                'is_connected' => false,
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Déconnecté',
        ]);
    }

    // ─────────────────────────────────────────
    // MÉTHODES PRIVÉES
    // ─────────────────────────────────────────

    private function validateToken(Request $request): bool
    {
        return $request->header('X-WEBHOOK-TOKEN') === $this->secretToken;
    }

    private function validateHWID(MT5Account $account, string $hwid): bool
    {
        if (empty($account->hwid)) {
            $account->update(['hwid' => $hwid]);
            return true;
        }

        return hash_equals($account->hwid, $hwid);
    }

    private function errorResponse(string $message, int $code)
    {
        return response()->json([
            'status'     => 'error',
            'authorized' => false,
            'message'    => $message,
        ], $code);
    }
}
