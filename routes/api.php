// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MT5Controller;


    // Webhook MT5
    Route::post('/mt5/webhook/trade', [MT5Controller::class, 'receiveTrade']);
    // Test de connexion (sans authentification)
    Route::post('/mt5/test-connection', [MT5Controller::class, 'testConnection']);

    // 📊 Statut du serveur (sans authentification)
    Route::get('/mt5/status', [MT5Controller::class, 'serverStatus']);
    Route::post('/mt5/webhook', [MT5Controller::class, 'receiveData']);

// Routes API protégées
Route::middleware(['auth:sanctum'])->group(function () {
    // Synchronisation MT5
    Route::post('/sync-account/{account}', [DashboardController::class, 'syncAccount']);

    // Récupérer les trades
    Route::get('/account-trades/{account}', [DashboardController::class, 'getAccountTrades']);



    // Statistiques en temps réel
    Route::get('/real-time-stats', function () {
        $user = auth()->user();

        $accounts = \App\Models\MT5Account::where('user_id', $user->id)->get();
        $totalEquity = $accounts->sum('equity');
        $totalBalance = $accounts->sum('balance');
        $totalProfit = \App\Models\Trade::where('user_id', $user->id)
            ->whereDate('open_time', today())
            ->sum('profit');

        return response()->json([
            'equity' => $totalEquity,
            'balance' => $totalBalance,
            'daily_profit' => $totalProfit,
            'accounts' => $accounts->count(),
            'last_updated' => now()->format('H:i:s'),
        ]);
    });

    // Alertes
    Route::get('/alerts', function () {
        $alerts = \App\Models\Alert::where('user_id', auth()->id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($alerts);
    });

    Route::post('/alerts/{alert}/read', function ($alertId) {
        $alert = \App\Models\Alert::findOrFail($alertId);

        if ($alert->user_id !== auth()->id()) {
            abort(403);
        }

        $alert->update(['is_read' => true]);

        return response()->json(['success' => true]);
    });
});
