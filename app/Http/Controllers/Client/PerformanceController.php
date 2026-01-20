<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\MT5Service;
use App\Models\Trade;
use App\Models\MT5Account;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Récupérer le compte sélectionné ou le premier compte
        $accountNumber = $request->get('account');
        $accounts = MT5Account::where('user_id', $user->id)->get();

        if ($accountNumber && $accounts->contains('account_number', $accountNumber)) {
            $selectedAccount = $accountNumber;
        } else {
            $selectedAccount = $accounts->first()->account_number ?? null;
        }

        // Période par défaut : 30 derniers jours
        $period = $request->get('period', '30d');
        list($startDate, $endDate) = $this->getDateRange($period);

        // Récupérer les trades
        $trades = Trade::where('user_id', $user->id)
            ->when($selectedAccount, function($query) use ($selectedAccount) {
                return $query->where('account_number', $selectedAccount);
            })
            ->whereBetween('open_time', [$startDate, $endDate])
            ->orderBy('open_time', 'desc')
            ->get();

        // Calculer les statistiques de performance
        $performanceData = $this->calculatePerformanceMetrics($trades);

        // Performance mensuelle - CORRECTION ICI
        $monthlyPerformance = $this->calculateMonthlyPerformance($user->id, $selectedAccount);

        // Trades récents pour affichage
        $recentTrades = $trades->take(20);

        // AJOUTER CETTE LIGNE POUR DÉBOGUER
        // dd($monthlyPerformance); // À commenter après vérification

        return view('client.performance', compact(
            'user', 'accounts', 'selectedAccount', 'performanceData',
            'monthlyPerformance', 'recentTrades', 'period' // <-- Inclure monthlyPerformance
        ));
    }

    private function getDateRange($period)
    {
        $endDate = Carbon::now()->endOfDay();

        switch ($period) {
            case '7d':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                break;
            case '30d':
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                break;
            case '90d':
                $startDate = Carbon::now()->subDays(90)->startOfDay();
                break;
            case '1y':
                $startDate = Carbon::now()->subYear()->startOfDay();
                break;
            case 'all':
                $startDate = Carbon::create(2020, 1, 1)->startOfDay();
                break;
            default:
                $startDate = Carbon::now()->subDays(30)->startOfDay();
        }

        return [$startDate, $endDate];
    }

    private function calculatePerformanceMetrics($trades)
    {
        $totalTrades = $trades->count();
        $profitableTrades = $trades->where('result', 'profit')->count();
        $losingTrades = $trades->where('result', 'loss')->count();
        $breakevenTrades = $trades->where('result', 'breakeven')->count();

        // Calcul du taux de gain
        $winRate = $totalTrades > 0 ? ($profitableTrades / $totalTrades) * 100 : 0;

        // Calcul du profit total
        $totalProfit = $trades->where('result', 'profit')->sum('profit');
        $totalLoss = abs($trades->where('result', 'loss')->sum('profit'));

        // Facteur de profit
        $profitFactor = $totalLoss > 0 ? $totalProfit / $totalLoss : ($totalProfit > 0 ? 999 : 0);

        // Gains et pertes moyens
        $averageWin = $profitableTrades > 0 ? $totalProfit / $profitableTrades : 0;
        $averageLoss = $losingTrades > 0 ? $totalLoss / $losingTrades : 0;

        // Ratio risque/récompense
        $riskRewardRatio = $averageLoss > 0 ? $averageWin / $averageLoss : 0;

        // Calcul du drawdown
        $drawdown = $this->calculateMaxDrawdown($trades);

        // Rendement mensuel (approximation)
        $monthlyReturn = $this->calculateMonthlyReturn($trades);

        return [
            'total_trades' => $totalTrades,
            'profitable_trades' => $profitableTrades,
            'losing_trades' => $losingTrades,
            'breakeven_trades' => $breakevenTrades,
            'win_rate' => round($winRate, 1),
            'total_profit' => $totalProfit,
            'total_loss' => $totalLoss,
            'net_profit' => $totalProfit - $totalLoss,
            'profit_factor' => round($profitFactor, 2),
            'average_win' => round($averageWin, 2),
            'average_loss' => round($averageLoss, 2),
            'risk_reward_ratio' => round($riskRewardRatio, 2),
            'max_drawdown' => round($drawdown, 1),
            'monthly_return' => round($monthlyReturn, 1),
            'largest_win' => $trades->where('result', 'profit')->max('profit') ?? 0,
            'largest_loss' => $trades->where('result', 'loss')->min('profit') ?? 0,
        ];
    }

    private function calculateMaxDrawdown($trades)
    {
        if ($trades->isEmpty()) return 0;

        $trades = $trades->sortBy('open_time');
        $equity = 0;
        $peak = 0;
        $maxDrawdown = 0;

        foreach ($trades as $trade) {
            $equity += $trade->profit;
            if ($equity > $peak) {
                $peak = $equity;
            }

            $drawdown = $peak > 0 ? (($peak - $equity) / $peak) * 100 : 0;
            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }

        return $maxDrawdown;
    }

    private function calculateMonthlyReturn($trades)
    {
        if ($trades->isEmpty()) return 0;

        $firstTrade = $trades->sortBy('open_time')->first();
        $lastTrade = $trades->sortByDesc('open_time')->first();

        $daysDiff = $firstTrade->open_time->diffInDays($lastTrade->open_time);
        $monthsTraded = max(1, $daysDiff / 30.44); // Nombre moyen de jours par mois

        $netProfit = $trades->sum('profit');
        $initialCapital = 10000; // Capital initial estimé

        $totalReturn = ($netProfit / $initialCapital) * 100;
        $monthlyReturn = $totalReturn / $monthsTraded;

        return $monthlyReturn;
    }

    private function calculateMonthlyPerformance($userId, $accountNumber = null)
    {
        $monthlyData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $trades = Trade::where('user_id', $userId)
                ->when($accountNumber, function($query) use ($accountNumber) {
                    return $query->where('account_number', $accountNumber);
                })
                ->whereBetween('open_time', [$startOfMonth, $endOfMonth])
                ->get();

            $profit = $trades->sum('profit');
            $initialCapital = 10000; // Capital initial estimé
            $return = $initialCapital > 0 ? ($profit / $initialCapital) * 100 : 0;

            $monthlyData[$month->format('M')] = round($return, 1);
        }

        return $monthlyData;
    }

    /**
     * Exporter les données de performance
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $trades = Trade::where('user_id', $user->id)
            ->when($request->account, function($query) use ($request) {
                return $query->where('account_number', $request->account);
            })
            ->when($request->start_date, function($query) use ($request) {
                return $query->where('open_time', '>=', $request->start_date);
            })
            ->when($request->end_date, function($query) use ($request) {
                return $query->where('open_time', '<=', $request->end_date);
            })
            ->orderBy('open_time', 'desc')
            ->get();

        // Générer un fichier CSV
        $fileName = 'performance_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($trades) {
            $file = fopen('php://output', 'w');

            // En-têtes
            fputcsv($file, [
                'Ticket', 'Date', 'Symbol', 'Type', 'Volume',
                'Open Price', 'Close Price', 'Profit', 'Result'
            ]);

            // Données
            foreach ($trades as $trade) {
                fputcsv($file, [
                    $trade->ticket,
                    $trade->open_time->format('Y-m-d H:i:s'),
                    $trade->symbol,
                    $trade->type,
                    $trade->volume,
                    $trade->open_price,
                    $trade->close_price,
                    $trade->profit,
                    $trade->result,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
