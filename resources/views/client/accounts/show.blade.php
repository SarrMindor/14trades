@extends('layouts.app')

@section('title', 'Détails du compte MT5')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Compte MT5 #{{ $account->account_number }}
                        </h4>
                        <div>
                            <a href="{{ route('client.accounts.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            @if($account->status == 'active')
                                <form action="{{ route('client.accounts.sync', $account->id) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="fas fa-sync me-1"></i> Synchroniser
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Messages d'alerte -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Bannière d'état -->
                        @if($account->status == 'inactive')
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Compte en attente de vérification</strong>
                                <p class="mb-0 mt-2">Votre compte est en cours de vérification par notre équipe.
                                    Vous recevrez un email avec les identifiants de connexion une fois la vérification terminée.</p>
                            </div>
                        @elseif($account->status == 'suspended')
                            <div class="alert alert-danger">
                                <i class="fas fa-ban me-2"></i>
                                <strong>Compte suspendu</strong>
                                <p class="mb-0 mt-2">Votre compte a été suspendu. Contactez le support pour plus d'informations.</p>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Informations du compte -->
                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations du compte
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Numéro de compte:</strong></td>
                                                <td>{{ $account->account_number }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Statut:</strong></td>
                                                <td>
                                                <span class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'inactive' ? 'warning' : 'danger') }}">
                                                    @if($account->status == 'active')
                                                        <i class="fas fa-check-circle me-1"></i>Actif
                                                    @elseif($account->status == 'inactive')
                                                        <i class="fas fa-clock me-1"></i>Inactif
                                                    @else
                                                        <i class="fas fa-ban me-1"></i>Suspendu
                                                    @endif
                                                </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date d'ajout:</strong></td>
                                                <td>{{ $account->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dernière mise à jour:</strong></td>
                                                <td>
                                                    @if($account->last_sync)
                                                        {{ $account->last_sync->format('d/m/Y H:i') }}
                                                    @else
                                                        <span class="text-muted">Jamais</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Notes:</strong></td>
                                                <td>
                                                    @if($account->notes)
                                                        <small class="text-muted">{{ $account->notes }}</small>
                                                    @else
                                                        <span class="text-muted">Aucune note</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Actions rapides -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-bolt me-2"></i>Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            @if($account->status == 'active')
                                                <button class="btn btn-success">
                                                    <i class="fas fa-sync me-2"></i>Synchroniser maintenant
                                                </button>
                                                <button class="btn btn-info">
                                                    <i class="fas fa-chart-bar me-2"></i>Voir les statistiques détaillées
                                                </button>
                                            @endif

                                            @if($account->status == 'inactive')
                                                <button class="btn btn-warning" disabled>
                                                    <i class="fas fa-clock me-2"></i>En attente d'activation
                                                </button>
                                            @endif

                                            <form action="{{ route('client.accounts.destroy', $account->id) }}" method="POST"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-trash me-2"></i>Supprimer ce compte
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques financières -->
                            <div class="col-lg-8">
                                <div class="row mb-4">
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6 class="mb-2">Balance</h6>
                                                <h3>${{ number_format($account->balance, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="mb-2">Equity</h6>
                                                <h3>${{ number_format($account->equity, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6 class="mb-2">Marge Libre</h6>
                                                <h3>${{ number_format($account->free_margin, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6 class="mb-2">Niveau Marge</h6>
                                                <h3>{{ number_format($stats['margin_level'], 2) }}%</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Graphique de performance -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chart-line me-2"></i>Évolution du compte
                                        </h6>
                                        <small>30 derniers jours</small>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="performanceChart" height="150"></canvas>
                                    </div>
                                </div>

                                <!-- Détails supplémentaires -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-cogs me-2"></i>Paramètres
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Effet de levier:</strong></td>
                                                        <td>1:{{ $account->leverage }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Devise:</strong></td>
                                                        <td>{{ $account->currency }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Marge:</strong></td>
                                                        <td>${{ number_format($account->margin, 2) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-chart-pie me-2"></i>Performances
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Profit/Perte:</strong></td>
                                                        <td>
                                                        <span class="badge bg-{{ $stats['profit_loss'] >= 0 ? 'success' : 'danger' }}">
                                                            ${{ number_format($stats['profit_loss'], 2) }}
                                                        </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Pourcentage:</strong></td>
                                                        <td>
                                                        <span class="badge bg-{{ $stats['profit_percentage'] >= 0 ? 'success' : 'danger' }}">
                                                            {{ number_format($stats['profit_percentage'], 2) }}%
                                                        </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Marge libre:</strong></td>
                                                        <td>{{ number_format($stats['free_margin_percentage'], 2) }}%</td>
                                                    </tr>
                                                </table>
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

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Données du graphique
            const history = @json($history);

            const dates = history.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('fr-FR', { month: 'short', day: 'numeric' });
            });

            const balances = history.map(item => item.balance);
            const changes = history.map(item => item.change);

            // Configuration du graphique
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Balance ($)',
                        data: balances,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const change = changes[index];
                                    const changePercent = history[index].change_percent;

                                    let label = context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                                    label += ' | Variation: ' + (change >= 0 ? '+' : '') + '$' + change.toFixed(2);
                                    label += ' (' + (changePercent >= 0 ? '+' : '') + changePercent.toFixed(2) + '%)';

                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
                            }
                        },
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'nearest'
                    }
                }
            });

            // Animation pour les cartes de statistiques
            const statCards = document.querySelectorAll('.col-md-3 .card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>

    <style>
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        }

        .card-header {
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        .table-sm td {
            padding: 0.5rem;
        }

        .badge {
            font-size: 0.85em;
            padding: 0.4em 0.8em;
        }

        /* Animation pour les valeurs */
        h3 {
            transition: all 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .row .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
