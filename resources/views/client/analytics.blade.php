@extends('layouts.app')

@section('title', 'Analytiques & Statistiques')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="bi bi-pie-chart me-2"></i>
                    Analytiques & Statistiques
                </h1>
                <button class="btn btn-secondary">
                    <i class="bi bi-download me-1"></i> Exporter les données
                </button>
            </div>
            <div class="card-body">
                <p style="color: var(--muted); margin-bottom: 25px;">
                    Statistiques détaillées de votre activité de trading. Analysez vos performances par compte, paire de devises et timeframe.
                </p>

                <!-- Performance par compte -->
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 18px;">
                            <i class="bi bi-wallet2 me-2"></i>
                            Performance par Compte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Compte</th>
                                    <th>Solde</th>
                                    <th>Équité</th>
                                    <th>Profit</th>
                                    <th>Performance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($analytics['accounts'] ?? [] as $account)
                                    <tr>
                                        <td style="font-weight: 600;">{{ $account['name'] }}</td>
                                        <td>${{ number_format($account['balance'], 2) }}</td>
                                        <td>${{ number_format($account['equity'], 2) }}</td>
                                        <td>
                                        <span style="color: {{ $account['profit'] >= 0 ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                                            {{ $account['profit'] >= 0 ? '+' : '' }}${{ number_format($account['profit'], 2) }}
                                        </span>
                                        </td>
                                        <td>
                                            @php
                                                $performance = ($account['equity'] - $account['balance']) / $account['balance'] * 100;
                                            @endphp
                                            <span style="color: {{ $performance >= 0 ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                                            {{ $performance >= 0 ? '+' : '' }}{{ number_format($performance, 2) }}%
                                        </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center" style="color: var(--muted); padding: 40px;">
                                            Aucun compte disponible pour l'analyse
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Performance par paire -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card" style="margin-bottom: 30px;">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 18px;">
                                    <i class="bi bi-currency-exchange me-2"></i>
                                    Performance par Paire
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Paire</th>
                                            <th>Nombre de Trades</th>
                                            <th>Profit Total</th>
                                            <th>Taux de Gain</th>
                                            <th>Performance</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($analytics['symbols'] ?? [] as $symbol)
                                            <tr>
                                                <td style="font-weight: 600;">{{ $symbol['name'] }}</td>
                                                <td>{{ $symbol['trades'] }}</td>
                                                <td>
                                                <span style="color: {{ $symbol['profit'] >= 0 ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                                                    {{ $symbol['profit'] >= 0 ? '+' : '' }}${{ number_format($symbol['profit'], 2) }}
                                                </span>
                                                </td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <span>{{ $symbol['win_rate'] }}%</span>
                                                        <div class="progress" style="flex: 1; height: 6px; background: rgba(255,255,255,.1);">
                                                            <div class="progress-bar"
                                                                 style="width: {{ $symbol['win_rate'] }}%;
                                                                    background: linear-gradient(90deg,
                                                                    {{ $symbol['win_rate'] >= 70 ? 'var(--success)' : ($symbol['win_rate'] >= 60 ? 'var(--gold)' : 'var(--error)') }},
                                                                    {{ $symbol['win_rate'] >= 70 ? '#27ae60' : ($symbol['win_rate'] >= 60 ? '#f1c40f' : '#c0392b') }});">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($symbol['win_rate'] >= 70)
                                                        <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success);">
                                                        <i class="bi bi-star-fill me-1"></i> Excellent
                                                    </span>
                                                    @elseif($symbol['win_rate'] >= 60)
                                                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);">
                                                        <i class="bi bi-check-circle me-1"></i> Bon
                                                    </span>
                                                    @else
                                                        <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error);">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> À améliorer
                                                    </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center" style="color: var(--muted); padding: 40px;">
                                                    Aucune donnée de trading disponible
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card" style="margin-bottom: 30px;">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 18px;">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Performance par Timeframe
                                </h3>
                            </div>
                            <div class="card-body">
                                @forelse($analytics['timeframes'] ?? [] as $tf)
                                    <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--line);">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                            <span style="font-weight: 600; color: var(--text);">{{ $tf['name'] }}</span>
                                            <span style="color: {{ $tf['profit'] >= 0 ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                                        {{ $tf['profit'] >= 0 ? '+' : '' }}${{ number_format($tf['profit'], 2) }}
                                    </span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--muted);">
                                            <span>{{ $tf['trades'] }} trades</span>
                                            <span>{{ $tf['win_rate'] }}% de gain</span>
                                        </div>
                                    </div>
                                @empty
                                    <div style="color: var(--muted); text-align: center; padding: 30px;">
                                        Aucune donnée de timeframe
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Insights analytiques -->
                        <div class="card" style="background: rgba(26,35,126,.1); border-color: rgba(26,35,126,.3);">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 18px; color: #3498db;">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Insights
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="color: var(--muted); font-size: 14px; line-height: 1.6;">
                                    <p>📊 <strong>Meilleure paire :</strong>
                                        @php
                                            $bestSymbol = collect($analytics['symbols'] ?? [])->sortByDesc('win_rate')->first();
                                        @endphp
                                        {{ $bestSymbol['name'] ?? 'N/A' }} ({{ $bestSymbol['win_rate'] ?? 0 }}%)
                                    </p>
                                    <p>⏰ <strong>Meilleur timeframe :</strong>
                                        @php
                                            $bestTF = collect($analytics['timeframes'] ?? [])->sortByDesc('profit')->first();
                                        @endphp
                                        {{ $bestTF['name'] ?? 'N/A' }} (+${{ $bestTF['profit'] ?? 0 }})
                                    </p>
                                    <p>📈 <strong>Recommandation :</strong> Concentrez-vous sur vos paires et timeframes les plus rentables.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé statistique -->
                <div class="card" style="margin-top: 20px; border-left: 4px solid var(--gold);">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 18px;">
                            <i class="bi bi-clipboard-data me-2"></i>
                            Résumé Statistique
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <div style="text-align: center; padding: 15px;">
                                    <div style="font-size: 24px; font-weight: 700; color: var(--gold);">
                                        @php
                                            $totalTrades = array_sum(array_column($analytics['symbols'] ?? [], 'trades'));
                                        @endphp
                                        {{ $totalTrades }}
                                    </div>
                                    <div style="color: var(--muted); font-size: 13px; margin-top: 5px;">Trades Totaux</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div style="text-align: center; padding: 15px;">
                                    <div style="font-size: 24px; font-weight: 700; color: var(--success);">
                                        @php
                                            $totalProfit = array_sum(array_column($analytics['symbols'] ?? [], 'profit'));
                                        @endphp
                                        +${{ number_format($totalProfit, 2) }}
                                    </div>
                                    <div style="color: var(--muted); font-size: 13px; margin-top: 5px;">Profit Total</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div style="text-align: center; padding: 15px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #3498db;">
                                        @php
                                            $avgWinRate = count($analytics['symbols'] ?? []) > 0
                                                ? array_sum(array_column($analytics['symbols'], 'win_rate')) / count($analytics['symbols'])
                                                : 0;
                                        @endphp
                                        {{ number_format($avgWinRate, 1) }}%
                                    </div>
                                    <div style="color: var(--muted); font-size: 13px; margin-top: 5px;">Taux de Gain Moyen</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div style="text-align: center; padding: 15px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #9b59b6;">
                                        {{ count($analytics['accounts'] ?? []) }}
                                    </div>
                                    <div style="color: var(--muted); font-size: 13px; margin-top: 5px;">Comptes Actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
