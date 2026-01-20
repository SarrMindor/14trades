@extends('layouts.app')

@section('title', 'Historique des Paiements')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-history me-2"></i>Historique de Mes Paiements
                        </h4>
                    </div>

                    <div class="card-body">
                        @if($payments->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                                <h5>Aucun paiement trouvé</h5>
                                <p class="text-muted">Vous n'avez effectué aucun paiement pour le moment.</p>
                                <a href="{{ route('client.payments.checkout') }}" class="btn btn-primary mt-3">
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>#{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                            <span class="badge bg-success fs-6">
                                                {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                            </span>
                                            </td>
                                            <td>
                                                @php
                                                    $methodIcons = [
                                                        'wave' => 'fas fa-mobile-alt text-warning',
                                                        'orange' => 'fas fa-sim-card text-orange',
                                                        'mobile_money' => 'fas fa-money-bill-wave text-success'
                                                    ];
                                                    $methodLabels = [
                                                        'wave' => 'Wave',
                                                        'orange' => 'Orange Money',
                                                        'mobile_money' => 'Mobile Money'
                                                    ];
                                                @endphp
                                                <i class="{{ $methodIcons[$payment->method] ?? 'fas fa-money-bill' }} me-2"></i>
                                                {{ $methodLabels[$payment->method] ?? $payment->method }}
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'cancelled' => 'secondary'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'En attente',
                                                        'approved' => 'Approuvé',
                                                        'rejected' => 'Rejeté',
                                                        'cancelled' => 'Annulé'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                                            </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $payment->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('payment.proof.download', $payment->id) }}" class="btn btn-outline-success">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal pour détails -->
                                        <div class="modal fade" id="paymentModal{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Détails du Paiement #{{ $payment->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Montant:</strong></p>
                                                                <p><strong>Méthode:</strong></p>
                                                                <p><strong>Transaction ID:</strong></p>
                                                                <p><strong>Plan:</strong></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                                                <p>{{ $methodLabels[$payment->method] ?? $payment->method }}</p>
                                                                <p>{{ $payment->transaction_id ?? 'N/A' }}</p>
                                                                <p>
                                                                    @php
                                                                        $plan = match($payment->amount) {
                                                                            49 => 'Basic',
                                                                            99 => 'Normal',
                                                                            199 => 'Elite',
                                                                            default => 'Inconnu'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge bg-info">{{ $plan }}</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <p><strong>Statut:</strong></p>
                                                        <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }} fs-6">
                                                        {{ $statusLabels[$payment->status] ?? $payment->status }}
                                                    </span>

                                                        @if($payment->status == 'rejected' && $payment->admin_notes)
                                                            <div class="alert alert-danger mt-3">
                                                                <strong>Raison du rejet:</strong><br>
                                                                {{ $payment->admin_notes }}
                                                            </div>
                                                        @endif

                                                        <p class="mt-3"><strong>Date de soumission:</strong><br>
                                                            {{ $payment->created_at->format('d/m/Y à H:i') }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ route('payment.proof.download', $payment->id) }}" class="btn btn-success">
                                                            <i class="fas fa-download me-2"></i>Télécharger la preuve
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($payments->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $payments->links() }}
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                        </a>
                        @if($payments->isNotEmpty())
                            <a href="{{ route('payment.checkout') }}" class="btn btn-primary float-end">
                                <i class="fas fa-plus-circle me-2"></i>Nouveau paiement
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .text-orange {
            color: #FF6600 !important;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .modal-backdrop {
            opacity: 0.5;
        }
    </style>
@endsection
