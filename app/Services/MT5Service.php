<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\MT5Account;
use App\Models\MT5Connection;

class MT5Service
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.mt5.api_url');
        $this->apiKey = config('services.mt5.api_key');
    }

    /**
     * Récupérer les trades d'un compte MT5
     */
    public function getAccountTrades($accountNumber, $fromDate = null, $toDate = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/trades', [
                'account' => $accountNumber,
                'from_date' => $fromDate ?? now()->subDays(30)->format('Y-m-d'),
                'to_date' => $toDate ?? now()->format('Y-m-d'),
            ]);

            // Journaliser la connexion
            $this->logConnection(
                auth()->id(),
                $accountNumber,
                'get_trades',
                $response->successful() ? 'success' : 'error',
                $response->body()
            );

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'message' => 'Échec de la connexion MT5'];

        } catch (\Exception $e) {
            Log::error('MT5 API Error: ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Récupérer le solde et l'équité d'un compte
     */
    public function getAccountBalance($accountNumber)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/balance', [
                'account' => $accountNumber,
            ]);

            $this->logConnection(
                auth()->id(),
                $accountNumber,
                'get_balance',
                $response->successful() ? 'success' : 'error',
                $response->body()
            );

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'message' => 'Échec de la récupération du solde'];

        } catch (\Exception $e) {
            Log::error('MT5 Balance Error: ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ouvrir un trade via MT5
     */
    public function openTrade($accountNumber, $symbol, $type, $volume, $stopLoss = null, $takeProfit = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/open-trade', [
                'account' => $accountNumber,
                'symbol' => $symbol,
                'type' => strtoupper($type), // BUY or SELL
                'volume' => $volume,
                'stop_loss' => $stopLoss,
                'take_profit' => $takeProfit,
            ]);

            $this->logConnection(
                auth()->id(),
                $accountNumber,
                'open_trade',
                $response->successful() ? 'success' : 'error',
                $response->body()
            );

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'message' => 'Échec de l\'ouverture du trade'];

        } catch (\Exception $e) {
            Log::error('MT5 Open Trade Error: ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fermer un trade
     */
    public function closeTrade($accountNumber, $ticketId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/close-trade', [
                'account' => $accountNumber,
                'ticket' => $ticketId,
            ]);

            $this->logConnection(
                auth()->id(),
                $accountNumber,
                'close_trade',
                $response->successful() ? 'success' : 'error',
                $response->body()
            );

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'message' => 'Échec de la fermeture du trade'];

        } catch (\Exception $e) {
            Log::error('MT5 Close Trade Error: ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Journaliser les connexions MT5
     */
    private function logConnection($userId, $accountNumber, $action, $status, $response)
    {
        MT5Connection::create([
            'user_id' => $userId,
            'account_number' => $accountNumber,
            'action' => $action,
            'status' => $status,
            'response' => is_array($response) ? json_encode($response) : $response,
        ]);
    }

    /**
     * Synchroniser automatiquement les comptes MT5
     */
    public function syncUserAccounts($userId)
    {
        $accounts = MT5Account::where('user_id', $userId)->get();

        foreach ($accounts as $account) {
            // Récupérer le solde
            $balanceData = $this->getAccountBalance($account->account_number);

            if (!isset($balanceData['error'])) {
                $account->update([
                    'balance' => $balanceData['balance'] ?? $account->balance,
                    'equity' => $balanceData['equity'] ?? $account->equity,
                    'margin' => $balanceData['margin'] ?? $account->margin,
                    'free_margin' => $balanceData['free_margin'] ?? $account->free_margin,
                    'last_sync' => now(),
                ]);
            }
        }

        return true;
    }
}
