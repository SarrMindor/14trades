@extends('layouts.app')

@section('title', 'Gestion des Paiements - Admin')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1 class="card-title">
                        <i class="bi bi-cash-coin me-2"></i>
                        Gestion des Paiements
                    </h1>
                    <div class="admin-stats">
                    <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success); padding: 8px 16px; margin-right: 10px;">
    <i class="bi bi-check-circle me-1"></i>
    ${{ isset($verifiedAmount) ? number_format($verifiedAmount, 2) : '0.00' }} Vérifiés
</span>
                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold); padding: 8px 16px;">
                        <i class="bi bi-clock me-1"></i> ${{ number_format($pendingAmount, 2) }} En attente
                    </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Résumé financier -->
                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card">
                        <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
                        <div class="stat-label">Revenue Total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $totalTransactions }}</div>
                        <div class="stat-label">Transactions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${{ number_format($averageTransaction, 2) }}</div>
                        <div class="stat-label">Moyenne/Transaction</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $pendingCount }}</div>
                        <div class="stat-label">En Attente</div>
                    </div>
                </div>

                <!-- Filtres -->
                <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Tous les statuts</option>
                        <option value="verified">Vérifiés</option>
                        <option value="pending">En attente</option>
                        <option value="failed">Échoués</option>
                        <option value="refunded">Remboursés</option>
                    </select>

                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Toutes les méthodes</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank">Virement</option>
                        <option value="crypto">Crypto</option>
                    </select>

                    <input type="date" class="form-control" style="width: auto; min-width: 180px;" placeholder="Date de début">
                    <input type="date" class="form-control" style="width: auto; min-width: 180px;" placeholder="Date de fin">

                    <input type="text" class="form-control" placeholder="Rechercher..." style="flex: 1; min-width: 200px;">

                    <button class="btn btn-secondary">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>

                    <button class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Exporter
                    </button>
                </div>

                <!-- Liste des paiements -->
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Plan</th>
                            <th>Méthode</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <div style="font-family: monospace; color: var(--muted); font-size: 12px;">
                                        {{ substr($payment->transaction_id, 0, 8) }}...
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ strtoupper(substr($payment->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text);">{{ $payment->user->name }}</div>
                                            <div style="font-size: 12px; color: var(--muted);">{{ $payment->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text);">
                                        ${{ number_format($payment->amount, 2) }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--muted);">
                                        {{ $payment->currency }}
                                    </div>
                                </td>
                                <td>
                                <span class="badge" style="background: rgba(255,215,0,.1); color: var(--gold);
                                        padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                                    {{ $payment->plan ?? 'Normal' }}
                                </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        @if($payment->payment_method === 'stripe')
                                            <i class="bi bi-credit-card" style="color: #6772e5;"></i>
                                            <span>Stripe</span>
                                        @elseif($payment->payment_method === 'paypal')
                                            <i class="bi bi-paypal" style="color: #003087;"></i>
                                            <span>PayPal</span>
                                        @elseif($payment->payment_method === 'bank')
                                            <i class="bi bi-bank" style="color: var(--success);"></i>
                                            <span>Virement</span>
                                        @else
                                            <i class="bi bi-currency-bitcoin" style="color: #f7931a;"></i>
                                            <span>Crypto</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="color: var(--text); font-weight: 500;">
                                        {{ $payment->created_at->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--muted);">
                                        {{ $payment->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    @if($payment->status === 'verified')
                                        <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success);
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-check-circle me-1"></i> Vérifié
                                    </span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-clock me-1"></i> En attente
                                    </span>
                                    @elseif($payment->status === 'failed')
                                        <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error);
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-x-circle me-1"></i> Échoué
                                    </span>
                                    @else
                                        <span class="badge" style="background: rgba(155,89,182,.2); color: #9b59b6;
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-arrow-left-right me-1"></i> Remboursé
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Bouton Voir détails -->
                                        <button type="button" class="btn btn-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewPaymentModal{{ $payment->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Actions selon statut -->
                                        @if($payment->status === 'pending')
                                            <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm"
                                                        onclick="return confirm('Vérifier ce paiement ?')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Rejeter ce paiement ?')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Télécharger preuve -->
                                        @if($payment->proof_path)
                                            <a href="{{ route('admin.payments.proof', $payment->id) }}"
                                               class="btn btn-warning btn-sm" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endif

                                        <!-- Menu déroulant -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu" style="background: var(--panel); border: 1px solid var(--line);">
                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="bi bi-receipt me-2"></i> Générer facture
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="bi bi-envelope me-2"></i> Envoyer reçu
                                                    </a>
                                                </li>
                                                @if($payment->status === 'verified')
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#refundModal{{ $payment->id }}">
                                                            <i class="bi bi-arrow-left-right me-2"></i> Rembourser
                                                        </button>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="#" method="POST" onsubmit="return confirm('Supprimer cet enregistrement ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item" style="color: var(--error);">
                                                            <i class="bi bi-trash me-2"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Voir Paiement -->
                            <div class="modal fade" id="viewPaymentModal{{ $payment->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content" style="background: var(--panel); color: var(--text);">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Détails du Paiement</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Informations du Client</h6>
                                                    <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                                            <div class="user-avatar" style="width: 40px; height: 40px; font-size: 18px;">
                                                                {{ strtoupper(substr($payment->user->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <div style="font-weight: 600;">{{ $payment->user->name }}</div>
                                                                <div style="font-size: 13px; color: var(--muted);">{{ $payment->user->email }}</div>
                                                            </div>
                                                        </div>
                                                        <div style="font-size: 13px; color: var(--muted);">
                                                            Client depuis {{ $payment->user->created_at->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Détails du Paiement</h6>
                                                    <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                        <div class="row mb-2">
                                                            <div class="col-6" style="color: var(--muted);">ID Transaction:</div>
                                                            <div class="col-6" style="font-family: monospace;">{{ $payment->transaction_id }}</div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6" style="color: var(--muted);">Montant:</div>
                                                            <div class="col-6" style="font-weight: 700;">${{ number_format($payment->amount, 2) }}</div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6" style="color: var(--muted);">Méthode:</div>
                                                            <div class="col-6">{{ ucfirst($payment->payment_method) }}</div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6" style="color: var(--muted);">Date:</div>
                                                            <div class="col-6">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-6" style="color: var(--muted);">Statut:</div>
                                                            <div class="col-6">
                                                                @if($payment->status === 'verified')
                                                                    <span style="color: var(--success);">
                                                                    <i class="bi bi-check-circle"></i> Vérifié
                                                                </span>
                                                                @elseif($payment->status === 'pending')
                                                                    <span style="color: var(--gold);">
                                                                    <i class="bi bi-clock"></i> En attente
                                                                </span>
                                                                @else
                                                                    <span style="color: var(--error);">
                                                                    <i class="bi bi-x-circle"></i> {{ ucfirst($payment->status) }}
                                                                </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($payment->description)
                                                <div class="mt-3">
                                                    <h6>Description</h6>
                                                    <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                        {{ $payment->description }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($payment->metadata)
                                                <div class="mt-3">
                                                    <h6>Métadonnées</h6>
                                                    <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                        <pre style="color: var(--muted); font-size: 12px; margin: 0;">{{ json_encode(json_decode($payment->metadata), JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <button type="button" class="btn btn-primary">Générer facture</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Remboursement -->
                            @if($payment->status === 'verified')
                                <div class="modal fade" id="refundModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="background: var(--panel); color: var(--text);">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Remboursement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="#" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Client</label>
                                                        <input type="text" class="form-control" value="{{ $payment->user->name }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Montant du paiement</label>
                                                        <input type="text" class="form-control" value="${{ number_format($payment->amount, 2) }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Montant à rembourser</label>
                                                        <input type="number" step="0.01" max="{{ $payment->amount }}"
                                                               class="form-control" value="{{ $payment->amount }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Raison du remboursement</label>
                                                        <textarea class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-warning">Procéder au remboursement</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5" style="color: var(--muted);">
                                    <i class="bi bi-cash-coin" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>Aucun paiement trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($payments->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div style="color: var(--muted);">
                            Affichage de {{ $payments->firstItem() }} à {{ $payments->lastItem() }} sur {{ $payments->total() }} paiements
                        </div>
                        {{ $payments->links() }}
                    </div>
                @endif

                <!-- Statistiques supplémentaires -->
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title" style="font-size: 16px;">
                                    <i class="bi bi-pie-chart me-2"></i>
                                    Répartition par Méthode
                                </h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                    <!-- Graphique circulaire factice -->
                                    <div style="width: 150px; height: 150px; border-radius: 50%;
                                        background: conic-gradient(
                                            #6772e5 0% 45%,
                                            #003087 45% 75%,
                                            #2ecc71 75% 90%,
                                            #f7931a 90% 100%
                                        ); position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                            width: 80px; height: 80px; background: var(--panel); border-radius: 50%;">
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                                    <div style="text-align: center;">
                                        <div style="width: 12px; height: 12px; background: #6772e5; border-radius: 2px; margin: 0 auto 5px;"></div>
                                        <div style="font-size: 12px; color: var(--muted);">Stripe</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="width: 12px; height: 12px; background: #003087; border-radius: 2px; margin: 0 auto 5px;"></div>
                                        <div style="font-size: 12px; color: var(--muted);">PayPal</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="width: 12px; height: 12px; background: #2ecc71; border-radius: 2px; margin: 0 auto 5px;"></div>
                                        <div style="font-size: 12px; color: var(--muted);">Virement</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="width: 12px; height: 12px; background: #f7931a; border-radius: 2px; margin: 0 auto 5px;"></div>
                                        <div style="font-size: 12px; color: var(--muted);">Crypto</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title" style="font-size: 16px;">
                                    <i class="bi bi-calendar me-2"></i>
                                    Paiements Récent (7 jours)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; padding: 20px;">
                                    <!-- Graphique en barres factice -->
                                    <div style="display: flex; align-items: flex-end; height: 150px; gap: 15px;">
                                        @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                                                <div style="width: 20px; background: linear-gradient(to top, var(--gold), #ffed4e);
                                                height: {{ rand(30, 150) }}px; border-radius: 4px 4px 0 0;">
                                                </div>
                                                <div style="margin-top: 10px; color: var(--muted); font-size: 12px;">{{ $day }}</div>
                                            </div>
                                        @endforeach
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
            // Gestion des filtres de date
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                if (!input.value) {
                    const today = new Date().toISOString().split('T')[0];
                    if (input.placeholder.includes('début')) {
                        const weekAgo = new Date();
                        weekAgo.setDate(weekAgo.getDate() - 7);
                        input.value = weekAgo.toISOString().split('T')[0];
                    } else if (input.placeholder.includes('fin')) {
                        input.value = today;
                    }
                }
            });
        });
    </script>
@endpush
