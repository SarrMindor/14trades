@extends('layouts.app')

@section('title', 'Mes Comptes MT5')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Mes Comptes MT5
                        </h4>
                        <div>
                            @if($apiConnected)
                                <span class="badge bg-success me-2">
                                <i class="fas fa-wifi"></i> API Connectée
                            </span>
                            @else
                                <span class="badge bg-danger me-2">
                                <i class="fas fa-wifi-slash"></i> API Déconnectée
                            </span>
                            @endif
                            <a href="{{ route('client.accounts.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Ajouter un compte
                            </a>
                            <button class="btn btn-info btn-sm" id="syncAllBtn">
                                <i class="fas fa-sync me-1"></i> Tout synchroniser
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
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

                        @if($accounts->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                                <h5>Aucun compte MT5</h5>
                                <p class="text-muted">Commencez par ajouter votre premier compte MT5.</p>
                                <a href="{{ route('client.accounts.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Ajouter mon premier compte
                                </a>
                            </div>
                        @else
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary bg-opacity-10 border-primary">
                                        <div class="card-body">
                                            <h6 class="text-primary mb-1">Total des comptes</h6>
                                            <h3 class="mb-0">{{ $accounts->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success bg-opacity-10 border-success">
                                        <div class="card-body">
                                            <h6 class="text-success mb-1">Total Equity</h6>
                                            <h3 class="mb-0">${{ number_format($accounts->sum('equity'), 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info bg-opacity-10 border-info">
                                        <div class="card-body">
                                            <h6 class="text-info mb-1">Total Balance</h6>
                                            <h3 class="mb-0">${{ number_format($accounts->sum('balance'), 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning bg-opacity-10 border-warning">
                                        <div class="card-body">
                                            <h6 class="text-warning mb-1">Comptes actifs</h6>
                                            <h3 class="mb-0">{{ $accounts->where('is_active', true)->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Compte</th>
                                        <th>Serveur</th>
                                        <th>Balance</th>
                                        <th>Equity</th>
                                        <th>Marge</th>
                                        <th>Devise</th>
                                        <th>Statut</th>
                                        <th>Dernière synchro</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($accounts as $account)
                                        <tr>
                                            <td>
                                                <strong>{{ $account->formatted_account_number }}</strong>
                                                @if($account->name)
                                                    <br><small class="text-muted">{{ $account->name }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $account->server }}</td>
                                            <td>
                                            <span class="badge bg-secondary">
                                                ${{ number_format($account->balance, 2) }}
                                            </span>
                                            </td>
                                            <td>
                                            <span class="badge bg-{{ $account->is_profitable ? 'success' : 'danger' }}">
                                                ${{ number_format($account->equity, 2) }}
                                                @if($account->profit != 0)
                                                    <small>({{ $account->profit > 0 ? '+' : '' }}{{ number_format($account->profit, 2) }})</small>
                                                @endif
                                            </span>
                                            </td>
                                            <td>
                                            <span class="badge bg-info">
                                                {{ number_format($account->margin_level, 2) }}%
                                            </span>
                                            </td>
                                            <td>{{ $account->currency }}</td>
                                            <td>
                                            <span class="badge bg-{{ $account->status == 'active' ? 'success' : 'warning' }}">
                                                {{ $account->status }}
                                            </span>
                                            </td>
                                            <td>
                                                @if($account->last_sync)
                                                    <span class="text-muted" title="{{ $account->last_sync->format('d/m/Y H:i:s') }}">
                                                    {{ $account->last_sync->diffForHumans() }}
                                                </span>
                                                @else
                                                    <span class="text-muted">Jamais</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-outline-primary" title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form action="{{ route('client.accounts.sync', $account->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-info" title="Synchroniser">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('client.accounts.destroy', $account->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')"
                                                                title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bouton synchroniser tous
            document.getElementById('syncAllBtn').addEventListener('click', function() {
                if (confirm('Voulez-vous synchroniser tous vos comptes ?')) {
                    fetch('{{ route("client.accounts.syncAll") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            location.reload();
                        }
                    });
                }
            });

            // Auto-refresh toutes les 5 minutes
            setTimeout(function() {
                location.reload();
            }, 300000); // 5 minutes
        });
    </script>
@endsection
