<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MT5ApiService
{
    private $apiUrl;
    private $apiLogin;
    private $apiPassword;
    private $apiServer;

    public function __construct()
    {
        $this->apiUrl = config('services.mt5.api_url', 'https://your-mt5-server.com');
        $this->apiLogin = config('services.mt5.api_login');
        $this->apiPassword = config('services.mt5.api_password');
        $this->apiServer = config('services.mt5.server', 'Default');
    }

    /**
     * Tester la connexion à l'API MT5
     */
    public function testConnection()
    {
        try {
            $response = $this->makeRequest('GET', '/api/test');
            return $response !== null;
        } catch (\Exception $e) {
            Log::error('MT5 Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les informations d'un compte
     */
    public function getAccountInfo($accountNumber, $password)
    {
        $cacheKey = "mt5_account_{$accountNumber}_info";

        return Cache::remember($cacheKey, 60, function () use ($accountNumber, $password) {
            try {
                $response = $this->makeRequest('POST', '/api/account/info', [
                    'account' => $accountNumber,
                    'password' => $password,
                    'server' => $this->apiServer
                ]);

                if ($response && isset($response['success']) && $response['success']) {
                    return $response['data'];
                }

                return null;
            } catch (\Exception $e) {
                Log::error('MT5 Get Account Info Failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtenir les trades d'un compte
     */
    public function getAccountTrades($accountNumber, $fromDate = null, $toDate = null)
    {
        try {
            $params = [
                'account' => $accountNumber,
                'server' => $this->apiServer
            ];

            if ($fromDate) $params['from'] = $fromDate;
            if ($toDate) $params['to'] = $toDate;

            $response = $this->makeRequest('POST', '/api/account/trades', $params);

            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('MT5 Get Trades Failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir l'historique des comptes
     */
    public function getAccountHistory($accountNumber, $period = 'daily')
    {
        try {
            $response = $this->makeRequest('POST', '/api/account/history', [
                'account' => $accountNumber,
                'server' => $this->apiServer,
                'period' => $period
            ]);

            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('MT5 Get History Failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifier les identifiants d'un compte
     */
    public function verifyCredentials($accountNumber, $password)
    {
        try {
            $response = $this->makeRequest('POST', '/api/account/verify', [
                'account' => $accountNumber,
                'password' => $password,
                'server' => $this->apiServer
            ]);

            return $response && isset($response['success']) && $response['success'];
        } catch (\Exception $e) {
            Log::error('MT5 Verify Credentials Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchroniser un compte
     */
    public function syncAccount($accountNumber, $password)
    {
        try {
            // 1. Vérifier les identifiants
            if (!$this->verifyCredentials($accountNumber, $password)) {
                throw new \Exception('Identifiants invalides');
            }

            // 2. Obtenir les infos du compte
            $accountInfo = $this->getAccountInfo($accountNumber, $password);

            if (!$accountInfo) {
                throw new \Exception('Impossible de récupérer les informations du compte');
            }

            // 3. Obtenir les trades récents
            $trades = $this->getAccountTrades($accountNumber, now()->subDays(7)->format('Y-m-d'));

            return [
                'account_info' => $accountInfo,
                'recent_trades' => $trades,
                'sync_time' => now()->toDateTimeString()
            ];

        } catch (\Exception $e) {
            Log::error('MT5 Sync Account Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Faire une requête à l'API MT5
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        try {
            $url = rtrim($this->apiUrl, '/') . $endpoint;

            $response = Http::withOptions([
                'verify' => false, // Désactiver la vérification SSL pour le dev
                'timeout' => 30,
            ])->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiLogin . ':' . $this->apiPassword),
                'Content-Type' => 'application/json',
            ])->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('MT5 API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('MT5 Request Exception: ' . $e->getMessage());
            return null;
        }
    }
}
