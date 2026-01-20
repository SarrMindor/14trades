{{-- resources/views/client/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Client - 14Trades')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Tableau de Bord</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>

        <!-- Alertes -->
        @if(!auth()->user()->is_approved)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Votre compte est en attente d'approbation par l'administrateur.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @php
            $daysRemaining = null;
            if(auth()->user()->subscription_ends_at){
                $daysRemaining = now()->diffInDays(auth()->user()->subscription_ends_at, false);
                if($daysRemaining < 0) $daysRemaining = 0;
            }
        @endphp

        @if(auth()->user()->subscription_status === 'active' && $daysRemaining !== null)
            <div>Jours restants : {{ $daysRemaining }}</div>
        @endif            @if($daysRemaining < 0)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Votre abonnement a expiré il y a {{ abs($daysRemaining) }} jours.
                    <a href="{{ route('client.payment') }}" class="alert-link">Renouveler maintenant</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif($daysRemaining === 0)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Votre abonnement expire aujourd'hui !
                    <a href="{{ route('client.payment') }}" class="alert-link">Renouveler maintenant</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif($daysRemaining < 7)
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Votre abonnement expire dans {{ $daysRemaining }} jours.
                    <a href="{{ route('client.payment') }}" class="alert-link">Renouveler maintenant</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif($daysRemaining < 30)
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <i class="fas fa-clock me-2"></i>
                    Votre abonnement expire dans {{ $daysRemaining }} jours.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

        <!-- Statistiques -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">Votre Plan</div>
                                <div class="h2">{{ strtoupper($user->plan ?? 'Aucun') }}</div>
                            </div>
                            <i class="fas fa-crown fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="small text-white">Abonnement</span>@php
                            $user = auth()->user();
                        @endphp

                        @if($user->subscription_ends_at)
                            <div>Jours restants : {{ now()->diffInDays($user->subscription_ends_at) }}</div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">Comptes Actifs</div>
                                <div class="h2">{{ $stats['active_accounts'] ?? 0 }}</div>
                            </div>
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('client.accounts.index') }}">
                            Gérer les comptes
                        </a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">Paiements</div>
                                {{ $stats['verified_payments'] ?? 0 }}/{{ $stats['total_payments'] ?? 0 }}
                            </div>
                            <i class="fas fa-money-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="{{ route('client.payment') }}">
                            Effectuer un paiement
                        </a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">Statut</div>
                                <div class="h2">
                                    @if($user->is_approved)
                                        Actif
                                    @else
                                        En attente
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">
                        @if($user->is_approved)
                            <i class="fas fa-check-circle me-1"></i> Compte approuvé
                        @else
                            <i class="fas fa-clock me-1"></i> En cours de validation
                        @endif
                    </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comptes MT5 -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-1"></i>
                        Mes Comptes MT5
                        @if($user->subscription_status === 'active')
                            <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                <i class="fas fa-plus"></i> Ajouter un compte
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(isset($accounts) && $accounts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Compte MT5</th>
                                        <th>Statut</th>
                                        <th>Dernière activité</th>
                                        <th>HWID</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($accounts as $account)
                                        <tr>
                                            <td>
                                                <strong>{{ $account->account_id }}</strong>
                                            </td>
                                            <td>
                                                @if($account->is_active)
                                                    <span class="badge bg-success">ACTIF</span>
                                                @else
                                                    <span class="badge bg-warning">EN ATTENTE</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($account->accessLogs->count() > 0)
                                                    {{ $account->accessLogs->first()->created_at->diffForHumans() }}
                                                @else
                                                    <span class="text-muted">Jamais</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($account->hwid)
                                                    <span class="badge bg-info">VERROUILLÉ</span>
                                                @else
                                                    <span class="badge bg-secondary">NON LIÉ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$account->is_active)
                                                    <form action="{{ route('client.accounts.delete', $account->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Supprimer ce compte ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                                <h5>Aucun compte MT5 activé</h5>
                                <p class="text-muted mb-4">Activez votre premier compte MT5 pour commencer à trader</p>
                                @if($user->subscription_status === 'active')
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                        <i class="fas fa-plus me-2"></i> Activer un compte
                                    </button>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Vous devez avoir un abonnement actif pour ajouter des comptes.
                                        <a href="{{ route('client.payment') }}" class="alert-link">Souscrire maintenant</a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Derniers Paiements -->
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Derniers Paiements
                    </div>
                    <div class="card-body">
                        @if(isset($payments) && $payments->count() > 0)
                            <div class="list-group">
                                @foreach($payments as $payment)
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">${{ $payment->amount }}</h6>
                                            <small>{{ $payment->created_at->format('d/m') }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $payment->method === 'wave' ? 'success' : 'warning' }}">
                                    {{ strtoupper($payment->method) }}
                                </span>
                                            <span class="badge bg-{{ $payment->status === 'verified' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ strtoupper($payment->status) }}
                                </span>
                                        </div>
                                        @if($payment->proof_path)
                                            <small>
                                                <a href="{{ route('admin.payments.proof', $payment->id) }}" class="text-decoration-none">
                                                    <i class="fas fa-download me-1"></i> Preuve
                                                </a>
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun paiement pour le moment</p>
                                <a href="{{ route('client.payment') }}" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i> Effectuer un paiement
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Compte MT5 -->
    @if($user->subscription_status === 'active')
        <div class="modal fade" id="addAccountModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un Compte MT5</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('client.accounts.add') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Numéro de Compte MT5</label>
                                <input type="text" name="account_id" class="form-control"
                                       placeholder="Ex: 12345678" required
                                       pattern="[0-9]+" title="Uniquement des chiffres">
                                <div class="form-text">
                                    Assurez-vous que ce compte est bien à votre nom.
                                </div>
                            </div>
                            @php
                                // Si $accounts n'est pas défini, le récupérer
                                $userAccounts = $accounts ?? \App\Models\MT5Account::where('user_id', auth()->id())->get();
                                $accountsCount = $userAccounts->count();
                            @endphp

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Plan {{ strtoupper($user->plan ?? auth()->user()->plan) }}:</strong>
                                Vous avez utilisé {{ $accountsCount }} sur
                                @if(($user->plan ?? auth()->user()->plan) == 'basic')
                                    1 compte autorisé.
                                @elseif(($user->plan ?? auth()->user()->plan) == 'normal')
                                    3 comptes autorisés.
                                @else
                                    comptes illimités.
                                @endif

                                @if(($user->plan ?? auth()->user()->plan) == 'basic' && $accountsCount >= 1)
                                    <div class="mt-2">
                                        <a href="{{ route('client.payment') }}" class="btn btn-sm btn-primary">
                                            Mettre à niveau votre plan pour ajouter plus de comptes
                                        </a>
                                    </div>
                                @elseif(($user->plan ?? auth()->user()->plan) == 'normal' && $accountsCount >= 3)
                                    <div class="mt-2">
                                        <a href="{{ route('client.payment') }}" class="btn btn-sm btn-primary">
                                            Mettre à niveau votre plan pour ajouter plus de comptes
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

        <!-- Dans la section des statistiques -->
        <div class="stats-grid">
            <!-- Comptes Actifs -->
            <div class="stat-card">
                <div class="stat-value">{{ $stats['active_accounts'] }}</div>
                <div class="stat-label">Comptes Actifs</div>
                <div class="stat-detail" style="margin-top: 8px; font-size: 12px; color: var(--muted);">
                    Solde total: ${{ number_format($stats['total_balance'], 2) }}
                </div>
            </div>

            <!-- Total Trades -->
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_trades'] }}</div>
                <div class="stat-label">Trades Totaux</div>
                <div class="stat-detail" style="margin-top: 8px; font-size: 12px; color: var(--muted);">
                    Taux de gain: {{ $stats['success_rate'] }}%
                </div>
            </div>

            <!-- Profit/Perte -->
            <div class="stat-card">
                <div class="stat-value" style="color: {{ $stats['profit_loss'] >= 0 ? 'var(--success)' : 'var(--error)' }};">
                    ${{ number_format($stats['profit_loss'], 2) }}
                </div>
                <div class="stat-label">Profit/Perte Net</div>
                <div class="stat-detail" style="margin-top: 8px; font-size: 12px; color: var(--muted);">
                    {{ $stats['profit_loss'] >= 0 ? '🟢 Profitable' : '🔴 En perte' }}
                </div>
            </div>

            <!-- Équité Totale -->
            <div class="stat-card">
                <div class="stat-value">${{ number_format($stats['total_equity'], 2) }}</div>
                <div class="stat-label">Équité Totale</div>
                <div class="stat-detail" style="margin-top: 8px; font-size: 12px; color: var(--muted);">
                    Marge libre: ${{ number_format($accounts->sum('free_margin'), 2) }}
                </div>
            </div>
        </div>

        <!-- Section des comptes avec synchronisation -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-wallet2 me-2"></i>
                    Vos Comptes MT5
                </h2>
                <div class="header-actions">
                    <button class="btn btn-secondary btn-sm" onclick="syncAllAccounts()">
                        <i class="bi bi-arrow-clockwise"></i> Tout synchroniser
                    </button>
                    <span class="text-muted" style="font-size: 12px; margin-left: 10px;">
                Dernière synchro: {{ $accounts->max('last_sync')?->diffForHumans() ?? 'Jamais' }}
            </span>
                </div>
            </div>
            <div class="card-body">
                @if($accounts->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Broker</th>
                                <th>Solde</th>
                                <th>Équité</th>
                                <th>Marge Libre</th>
                                <th>Statut</th>
                                <th>Dernière synchro</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                <tr id="account-{{ $account->id }}">
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->broker }}</td>
                                    <td>${{ number_format($account->balance, 2) }}</td>
                                    <td>
                                <span class="{{ $account->equity > $account->balance ? 'text-success' : ($account->equity < $account->balance ? 'text-danger' : '') }}">
                                    ${{ number_format($account->equity, 2) }}
                                </span>
                                    </td>
                                    <td>${{ number_format($account->free_margin, 2) }}</td>
                                    <td>
                                        @if($account->status === 'active')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($account->status === 'inactive')
                                            <span class="badge bg-warning">Inactif</span>
                                        @else
                                            <span class="badge bg-danger">Suspendu</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($account->last_sync)
                                            <span class="text-muted">{{ $account->last_sync->diffForHumans() }}</span>
                                        @else
                                            <span class="text-danger">Jamais</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="syncAccount({{ $account->id }})">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                            <a href="{{ route('client.performance', ['account' => $account->account_number]) }}"
                                               class="btn btn-outline-info">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#accountDetailsModal{{ $account->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Message si aucun compte -->
                @endif
            </div>
        </div>

        <!-- Scripts AJAX pour la synchronisation -->
        @push('footer-scripts')
            <script>
                function syncAccount(accountId) {
                    const row = document.getElementById(`account-${accountId}`);
                    const syncBtn = row.querySelector('button[onclick*="syncAccount"]');

                    // Afficher un indicateur de chargement
                    const originalHTML = syncBtn.innerHTML;
                    syncBtn.innerHTML = '<i class="bi bi-hourglass"></i>';
                    syncBtn.disabled = true;

                    // Envoyer la requête AJAX
                    fetch(`/api/sync-account/${accountId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Mettre à jour la ligne avec les nouvelles données
                                updateAccountRow(accountId, data.data);
                                showToast('success', 'Compte synchronisé avec succès');
                            } else {
                                showToast('error', data.message || 'Erreur de synchronisation');
                            }
                        })
                        .catch(error => {
                            showToast('error', 'Erreur réseau');
                        })
                        .finally(() => {
                            // Restaurer le bouton
                            syncBtn.innerHTML = originalHTML;
                            syncBtn.disabled = false;
                        });
                }

                function syncAllAccounts() {
                    // Implémenter la synchronisation de tous les comptes
                }

                function updateAccountRow(accountId, data) {
                    const row = document.getElementById(`account-${accountId}`);

                    // Mettre à jour les cellules avec les nouvelles données
                    row.cells[2].textContent = '$' + parseFloat(data.balance).toFixed(2);
                    row.cells[3].textContent = '$' + parseFloat(data.equity).toFixed(2);
                    row.cells[4].textContent = '$' + parseFloat(data.free_margin).toFixed(2);
                    row.cells[6].innerHTML = '<span class="text-muted">À l\'instant</span>';
                }

                function showToast(type, message) {
                    // Implémenter une notification toast
                    alert(message); // Version simple pour l'instant
                }
            </script>
        @endpush
@endsection

@push('styles')
    <style>
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
            transition: box-shadow 0.3s ease;
        }
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0;
        }
        .list-group-item:first-child {
            border-top: none;
        }
    </style>
@endpush
