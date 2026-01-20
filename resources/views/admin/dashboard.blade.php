{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Admin - 14Trades')

@section('content')
    <div class="container-custom">
        <h1 class="mt-4" style="color: var(--gold);">Dashboard Admin</h1>

        <!-- Statistiques -->
        <div class="stats-grid">
            <!-- Clients Totaux -->
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_clients'] ?? 0 }}</div>
                <div class="stat-label">Clients Totaux</div>
                <div style="margin-top: 10px;">
                    <a href="{{ route('admin.clients') ?? '#' }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-eye"></i> Voir détails
                    </a>
                </div>
            </div>

            <!-- En Attente -->
            <div class="stat-card">
                <div class="stat-value">{{ $stats['pending_approvals'] ?? 0 }}</div>
                <div class="stat-label">En Attente</div>
                <div style="margin-top: 10px;">
                    <a href="{{ route('admin.clients') ?? '#' }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-check-circle"></i> Approuver
                    </a>
                </div>
            </div>

            <!-- Paiements en Attente -->
            <div class="stat-card">
                <div class="stat-value">{{ $stats['pending_payments'] ?? 0 }}</div>
                <div class="stat-label">Paiements en Attente</div>
                <div style="margin-top: 10px;">
                    <a href="{{ route('admin.payments') ?? '#' }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-cash-coin"></i> Vérifier
                    </a>
                </div>
            </div>

            <!-- Revenue Total -->
            <div class="stat-card">
                <div class="stat-value">${{ number_format($stats['total_earnings'] ?? 0, 2) }}</div>
                <div class="stat-label">Revenue Total</div>
                <div style="margin-top: 10px;">
                    <a href="{{ route('admin.payments') ?? '#' }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-graph-up-arrow"></i> Voir détails
                    </a>
                </div>
            </div>
        </div>

        <!-- Derniers Clients -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-people me-2"></i>
                    Derniers Clients Inscrits
                </h2>
            </div>
            <div class="card-body">
                @if(isset($recentClients) && $recentClients->count() > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentClients as $client)
                                <tr>
                                    <td>{{ $client->name ?? 'N/A' }}</td>
                                    <td>{{ $client->email ?? 'N/A' }}</td>
                                    <td>
                                        @if($client->is_approved ?? false)
                                            <span style="color: var(--success);">
                                                <i class="bi bi-check-circle-fill"></i> Approuvé
                                            </span>
                                        @else
                                            <span style="color: var(--gold);">
                                                <i class="bi bi-clock-fill"></i> En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(!($client->is_approved ?? true))
                                                <button type="button" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-info btn-sm">
                                                <i class="bi bi-bolt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people" style="font-size: 48px; color: var(--muted2); margin-bottom: 16px;"></i>
                        <p style="color: var(--muted);">Aucun client pour le moment</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Derniers Paiements -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-cash-coin me-2"></i>
                    Derniers Paiements
                </h2>
            </div>
            <div class="card-body">
                @if(isset($recentPayments) && $recentPayments->count() > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($payment->amount ?? 0, 2) }}</td>
                                    <td>
                                        @if(($payment->status ?? 'pending') == 'verified')
                                            <span style="color: var(--success);">
                                                <i class="bi bi-check-circle-fill"></i> Vérifié
                                            </span>
                                        @elseif(($payment->status ?? 'pending') == 'rejected')
                                            <span style="color: var(--error);">
                                                <i class="bi bi-x-circle-fill"></i> Rejeté
                                            </span>
                                        @else
                                            <span style="color: var(--gold);">
                                                <i class="bi bi-clock-fill"></i> En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($payment->status ?? 'pending') == 'pending')
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cash-coin" style="font-size: 48px; color: var(--muted2); margin-bottom: 16px;"></i>
                        <p style="color: var(--muted);">Aucun paiement pour le moment</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Comptes en Attente -->
        @if(isset($pendingAccounts) && $pendingAccounts->count() > 0)
            <div class="card" style="margin-top: 30px; border-left: 4px solid var(--gold);">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="bi bi-clock-history me-2"></i>
                        Comptes MT5 en Attente d'Activation
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Client</th>
                                <th>Compte MT5</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pendingAccounts as $account)
                                <tr>
                                    <td>{{ $account->user->name ?? 'N/A' }}</td>
                                    <td>{{ $account->account_id ?? 'N/A' }}</td>
                                    <td>{{ isset($account->created_at) ? $account->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm">
                                            <i class="bi bi-check"></i> Activer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser les tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
