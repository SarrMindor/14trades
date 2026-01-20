@extends('layouts.app')

@section('title', 'Analyse de Performance')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Analyse de Performance
                </h1>
                <div class="period-selector">
                    <select class="form-control" style="width: auto; background: rgba(5,9,20,.6); border-color: var(--line); color: var(--text);">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">90 derniers jours</option>
                        <option value="365">1 an</option>
                        <option value="all">Tout l'historique</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <p style="color: var(--muted); margin-bottom: 25px;">
                    Analyse détaillée de vos performances de trading. Suivez vos indicateurs clés et identifiez les points d'amélioration.
                </p>

                <!-- Indicateurs de performance -->
                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card">
                        <div class="stat-value">{{ $performanceData['monthly_return'] ?? 0 }}%</div>
                        <div class="stat-label">Rendement Mensuel</div>
                        <div class="stat-trend {{ ($performanceData['monthly_return'] ?? 0) >= 0 ? 'positive' : 'negative' }}"
                             style="margin-top: 8px; font-size: 12px;">
                            @if(($performanceData['monthly_return'] ?? 0) >= 0)
                                <i class="bi bi-arrow-up-right"></i> Performance positive
                            @else
                                <i class="bi bi-arrow-down-right"></i> Performance négative
                            @endif
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value">{{ $performanceData['win_rate'] ?? 0 }}%</div>
                        <div class="stat-label">Taux de Gain</div>
                        <div class="progress" style="height: 6px; margin-top: 10px; background: rgba(255,255,255,.1);">
                            <div class="progress-bar"
                                 style="width: {{ $performanceData['win_rate'] ?? 0 }}%; background: linear-gradient(90deg, var(--success), #27ae60);">
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value">{{ $performanceData['profit_factor'] ?? 0 }}</div>
                        <div class="stat-label">Facteur de Profit</div>
                        <div style="margin-top: 10px; font-size: 12px; color: {{ ($performanceData['profit_factor'] ?? 0) >= 1.5 ? 'var(--success)' : (($performanceData['profit_factor'] ?? 0) >= 1 ? 'var(--gold)' : 'var(--error)') }};">
                            @if(($performanceData['profit_factor'] ?? 0) >= 1.5)
                                <i class="bi bi-star-fill"></i> Excellent
                            @elseif(($performanceData['profit_factor'] ?? 0) >= 1)
                                <i class="bi bi-check-circle"></i> Satisfaisant
                            @else
                                <i class="bi bi-exclamation-triangle"></i> À améliorer
                            @endif
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value">{{ $performanceData['max_drawdown'] ?? 0 }}%</div>
                        <div class="stat-label">Drawdown Max</div>
                        <div style="margin-top: 10px; font-size: 12px; color: {{ abs($performanceData['max_drawdown'] ?? 0) <= 10 ? 'var(--success)' : (abs($performanceData['max_drawdown'] ?? 0) <= 20 ? 'var(--gold)' : 'var(--error)') }};">
                            @if(abs($performanceData['max_drawdown'] ?? 0) <= 10)
                                <i class="bi bi-shield-check"></i> Risque faible
                            @elseif(abs($performanceData['max_drawdown'] ?? 0) <= 20)
                                <i class="bi bi-shield"></i> Risque modéré
                            @else
                                <i class="bi bi-shield-exclamation"></i> Risque élevé
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Graphique de performance mensuelle -->
                <!-- Dans la section du graphique de performance mensuelle -->
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 18px;">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            Performance Mensuelle
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; position: relative;">
                            <!-- Graphique factice - À remplacer par Chart.js -->
                            <div style="display: flex; align-items: flex-end; height: 250px; gap: 20px; padding: 20px;">
                                @if(isset($monthlyPerformance) && count($monthlyPerformance) > 0)
                                    @foreach($monthlyPerformance as $month => $value)
                                        <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                                            <div style="width: 30px; background: {{ $value >= 0 ? 'linear-gradient(to top, var(--success), #2ecc71)' : 'linear-gradient(to top, var(--error), #e74c3c)' }};
                                    height: {{ min(abs($value) * 20, 200) }}px; border-radius: 4px 4px 0 0;">
                                            </div>
                                            <div style="margin-top: 10px; color: var(--muted); font-size: 12px;">{{ $month }}</div>
                                            <div style="color: {{ $value >= 0 ? 'var(--success)' : 'var(--error)' }}; font-size: 12px; font-weight: 600;">
                                                {{ $value >= 0 ? '+' : '' }}{{ $value }}%
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="width: 100%; text-align: center; color: var(--muted); padding: 100px 0;">
                                        <i class="bi bi-bar-chart" style="font-size: 48px; opacity: 0.5;"></i>
                                        <p>Aucune donnée de performance disponible</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails des trades -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 18px;">
                                    <i class="bi bi-pie-chart me-2"></i>
                                    Répartition des Trades
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: center; align-items: center; height: 200px;">
                                    <!-- Diagramme circulaire factice -->
                                    <div style="position: relative; width: 150px; height: 150px;">
                                        <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%;
                                            background: conic-gradient(
                                                var(--success) 0% {{ ($performanceData['profitable_trades'] ?? 0) / ($performanceData['total_trades'] ?? 1) * 100 }}%,
                                                var(--error) {{ ($performanceData['profitable_trades'] ?? 0) / ($performanceData['total_trades'] ?? 1) * 100 }}% {{ (($performanceData['profitable_trades'] ?? 0) + ($performanceData['losing_trades'] ?? 0)) / ($performanceData['total_trades'] ?? 1) * 100 }}%,
                                                var(--muted2) {{ (($performanceData['profitable_trades'] ?? 0) + ($performanceData['losing_trades'] ?? 0)) / ($performanceData['total_trades'] ?? 1) * 100 }}% 100%
                                            );">
                                        </div>
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                            background: var(--panel); width: 80px; height: 80px; border-radius: 50%;">
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                                    <div style="text-align: center;">
                                        <div style="color: var(--success); font-size: 20px; font-weight: 700;">
                                            {{ $performanceData['profitable_trades'] ?? 0 }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Gagnants</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="color: var(--error); font-size: 20px; font-weight: 700;">
                                            {{ $performanceData['losing_trades'] ?? 0 }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Perdants</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="color: var(--muted2); font-size: 20px; font-weight: 700;">
                                            {{ $performanceData['breakeven_trades'] ?? 0 }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Break-even</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 18px;">
                                    <i class="bi bi-cash-coin me-2"></i>
                                    Gains & Pertes Moyens
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 20px;">
                                    <div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <span style="color: var(--muted);">Gain moyen :</span>
                                            <span style="color: var(--success); font-weight: 600;">
                                            +${{ number_format($performanceData['average_win'] ?? 0, 2) }}
                                        </span>
                                        </div>
                                        <div class="progress" style="height: 8px; background: rgba(46,204,113,.2);">
                                            <div class="progress-bar" style="width: 100%; background: var(--success);"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <span style="color: var(--muted);">Perte moyenne :</span>
                                            <span style="color: var(--error); font-weight: 600;">
                                            ${{ number_format($performanceData['average_loss'] ?? 0, 2) }}
                                        </span>
                                        </div>
                                        <div class="progress" style="height: 8px; background: rgba(231,76,60,.2);">
                                            <div class="progress-bar" style="width: 100%; background: var(--error);"></div>
                                        </div>
                                    </div>

                                    <div style="margin-top: 20px; padding: 15px; background: rgba(255,215,0,.05);
                                        border-radius: 12px; border: 1px solid rgba(255,215,0,.2);">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <i class="bi bi-lightbulb" style="color: var(--gold);"></i>
                                            <div>
                                                <div style="color: var(--gold); font-weight: 600;">Conseil de performance</div>
                                                <div style="color: var(--muted); font-size: 14px; margin-top: 5px;">
                                                    {{ ($performanceData['average_win'] ?? 0) > abs($performanceData['average_loss'] ?? 0)
                                                        ? 'Vos gains sont supérieurs à vos pertes - excellente gestion du risque!'
                                                        : 'Essayez d\'améliorer votre ratio risque/récompense en prenant des profits plus tôt.' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script pour le sélecteur de période
            const periodSelect = document.querySelector('.period-selector select');
            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    // Ici tu pourrais rafraîchir les données selon la période
                    alert('Chargement des données pour ' + this.value + ' jours...');
                });
            }
        });
    </script>
@endpush
