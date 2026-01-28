<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicenseApiController extends Controller
{
    // Validation de licence pour MT5
    public function validateLicense(Request $request)
    {
        $account = $request->get('account');
        $server = $request->get('server');
        $hwid = $request->get('hwid');

        // Log de la requête
        $logMessage = date('Y-m-d H:i:s') . " | Account: {$account} | Server: {$server} | HWID: {$hwid}";

        // Chercher la licence
        $license = License::where('mt5_account', $account)
            ->where('server', $server)
            ->where('hwid', $hwid)
            ->where('is_active', true)
            ->first();

        if (!$license) {
            Log::channel('license_api')->info($logMessage . " | RESULT: NOT_FOUND");
            return response("NO;NOT_FOUND", 200, ['Content-Type' => 'text/plain']);
        }

        if ($license->expires_at->isPast()) {
            // Désactiver automatiquement la licence expirée
            $license->update(['is_active' => false]);

            Log::channel('license_api')->info($logMessage . " | RESULT: EXPIRED");
            return response("NO;EXPIRED", 200, ['Content-Type' => 'text/plain']);
        }

        // Incrémenter le compteur de validation
        $license->incrementValidation();

        // Formater la réponse
        $response = sprintf(
            "OK;%s;%s",
            strtoupper($license->plan),
            $license->expires_at->format('Y.m.d')
        );

        Log::channel('license_api')->info($logMessage . " | RESULT: " . $response);

        return response($response, 200, ['Content-Type' => 'text/plain']);
    }

    // API pour obtenir les infos de licence (JSON)
    public function getLicenseInfo(Request $request)
    {
        $license = License::where('mt5_account', $request->account)
            ->where('server', $request->server)
            ->where('hwid', $request->hwid)
            ->where('is_active', true)
            ->first();

        if (!$license || $license->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'License not found or expired'
            ], 404);
        }

        $license->incrementValidation();

        return response()->json([
            'valid' => true,
            'plan' => strtoupper($license->plan),
            'expires_at' => $license->expires_at->format('Y.m.d'),
            'days_left' => $license->expires_at->diffInDays(now()),
            'lot_multiplier' => $license->getLotMultiplier(),
            'user' => $license->user ? $license->user->name : 'Unknown',
        ]);
    }

    // API pour vérifier l'état du service
    public function healthCheck()
    {
        $activeLicenses = License::where('is_active', true)->count();
        $validationsToday = License::whereDate('last_validation', today())->count();

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'active_licenses' => $activeLicenses,
            'validations_today' => $validationsToday,
            'endpoint' => url('/api/license/validate'),
        ]);
    }
}
