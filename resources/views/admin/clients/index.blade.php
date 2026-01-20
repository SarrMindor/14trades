@extends('layouts.app')

@section('title', 'Gestion des Clients - Admin')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1 class="card-title">
                        <i class="bi bi-people me-2"></i>
                        Gestion des Clients
                    </h1>
                    <div class="admin-stats">
                    <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success); padding: 8px 16px; margin-right: 10px;">
                        <i class="bi bi-check-circle me-1"></i> {{ $activeClients }} Actifs
                    </span>
                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold); padding: 8px 16px;">
                        <i class="bi bi-clock me-1"></i> {{ $pendingClients }} En attente
                    </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtres -->
                <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actifs</option>
                        <option value="pending">En attente</option>
                        <option value="expired">Expirés</option>
                    </select>

                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Tous les plans</option>
                        <option value="basic">Basic</option>
                        <option value="normal">Normal</option>
                        <option value="elite">Elite</option>
                    </select>

                    <input type="text" class="form-control" placeholder="Rechercher un client..." style="flex: 1; min-width: 200px;">

                    <button class="btn btn-secondary">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>

                    <button class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Exporter
                    </button>
                </div>

                <!-- Liste des clients -->
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Plan</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>
                                    <input type="checkbox" class="client-checkbox" value="{{ $client->id }}">
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text);">{{ $client->name }}</div>
                                            <div style="font-size: 12px; color: var(--muted);">ID: {{ $client->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->phone ?? 'Non renseigné' }}</td>
                                <td>
                                <span class="badge" style="background: rgba(255,215,0,.1); color: var(--gold);
                                        padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                                    {{ $client->plan ?? 'Aucun' }}
                                </span>
                                </td>
                                <td>
                                    @if($client->is_approved)
                                        @if($client->subscription_status === 'active')
                                            <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-check-circle me-1"></i> Actif
                                        </span>
                                        @elseif($client->subscription_status === 'pending')
                                            <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-clock me-1"></i> En attente
                                        </span>
                                        @else
                                            <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-x-circle me-1"></i> Expiré
                                        </span>
                                        @endif
                                    @else
                                        <span class="badge" style="background: rgba(155,89,182,.2); color: #9b59b6;
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-person-x me-1"></i> Non approuvé
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="color: var(--text); font-weight: 500;">
                                        {{ $client->created_at->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--muted);">
                                        {{ $client->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Bouton Voir -->
                                        <button type="button" class="btn btn-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewClientModal{{ $client->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Bouton Modifier -->
                                        <button type="button" class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editClientModal{{ $client->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Actions rapides -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu" style="background: var(--panel); border: 1px solid var(--line);">
                                                @if(!$client->is_approved)
                                                    <li>
                                                        <form action="{{ route('admin.clients.approve', $client->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item" style="color: var(--success);">
                                                                <i class="bi bi-check-circle me-2"></i> Approuver
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if($client->is_approved && $client->subscription_status !== 'active')
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#activateSubscriptionModal{{ $client->id }}"
                                                                style="color: var(--gold);">
                                                            <i class="bi bi-bolt me-2"></i> Activer abonnement
                                                        </button>
                                                    </li>
                                                @endif

                                                <li><hr class="dropdown-divider"></li>

                                                <li>
                                                    <a class="dropdown-item" href="#" style="color: var(--text);">
                                                        <i class="bi bi-chat me-2"></i> Envoyer message
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" href="#" style="color: var(--text);">
                                                        <i class="bi bi-envelope me-2"></i> Email
                                                    </a>
                                                </li>

                                                <li><hr class="dropdown-divider"></li>

                                                <li>
                                                    <form action="#" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
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

                            <!-- Modal Voir Client -->
                            <div class="modal fade" id="viewClientModal{{ $client->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content" style="background: var(--panel); color: var(--text);">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Détails du Client</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="text-center mb-4">
                                                        <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto;">
                                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                                        </div>
                                                        <h5 class="mt-3">{{ $client->name }}</h5>
                                                        <div style="color: var(--muted);">ID: {{ $client->id }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Informations Personnelles</label>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">Email</div>
                                                                    <div style="color: var(--text);">{{ $client->email }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">Téléphone</div>
                                                                    <div style="color: var(--text);">{{ $client->phone ?? 'Non renseigné' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Statut d'Abonnement</label>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">Plan</div>
                                                                    <div style="color: var(--text); text-transform: uppercase;">
                                                                        {{ $client->plan ?? 'Aucun' }}
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">Statut</div>
                                                                    <div>
                                                                        @if($client->subscription_status === 'active')
                                                                            <span style="color: var(--success);">
                                                                            <i class="bi bi-check-circle"></i> Actif
                                                                        </span>
                                                                        @elseif($client->subscription_status === 'pending')
                                                                            <span style="color: var(--gold);">
                                                                            <i class="bi bi-clock"></i> En attente
                                                                        </span>
                                                                        @else
                                                                            <span style="color: var(--error);">
                                                                            <i class="bi bi-x-circle"></i> Expiré
                                                                        </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="form-label">Informations Techniques</label>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">Dernière connexion</div>
                                                                    <div style="color: var(--text);">{{ $client->last_login_at?->diffForHumans() ?? 'Jamais' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div style="color: var(--muted); font-size: 13px;">IP</div>
                                                                    <div style="color: var(--text);">{{ $client->last_login_ip ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <button type="button" class="btn btn-primary">Envoyer message</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Activer Abonnement -->
                            <div class="modal fade" id="activateSubscriptionModal{{ $client->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="background: var(--panel); color: var(--text);">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Activer l'Abonnement</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.clients.activate', $client->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Client</label>
                                                    <input type="text" class="form-control" value="{{ $client->name }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Plan</label>
                                                    <select name="plan" class="form-select" required>
                                                        <option value="basic">Basic - $49/mois</option>
                                                        <option value="normal" selected>Normal - $99/mois</option>
                                                        <option value="elite">Elite - $199/mois</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Durée (mois)</label>
                                                    <input type="number" name="months" class="form-control" value="1" min="1" max="12" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Date de début</label>
                                                    <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Activer l'abonnement</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5" style="color: var(--muted);">
                                    <i class="bi bi-people" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>Aucun client trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($clients->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div style="color: var(--muted);">
                            Affichage de {{ $clients->firstItem() }} à {{ $clients->lastItem() }} sur {{ $clients->total() }} clients
                        </div>
                        <nav>
                            <ul class="pagination">
                                @if($clients->onFirstPage())
                                    <li class="page-item disabled">
                                <span class="page-link" style="background: var(--panel); border-color: var(--line); color: var(--muted);">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $clients->previousPageUrl() }}"
                                           style="background: var(--panel); border-color: var(--line); color: var(--text);">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach(range(1, $clients->lastPage()) as $page)
                                    @if($page == $clients->currentPage())
                                        <li class="page-item active">
                                    <span class="page-link" style="background: var(--gold); border-color: var(--gold);">
                                        {{ $page }}
                                    </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $clients->url($page) }}"
                                               style="background: var(--panel); border-color: var(--line); color: var(--text);">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach

                                @if($clients->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $clients->nextPageUrl() }}"
                                           style="background: var(--panel); border-color: var(--line); color: var(--text);">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                <span class="page-link" style="background: var(--panel); border-color: var(--line); color: var(--muted);">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif

                <!-- Actions en masse -->
                <div style="margin-top: 30px; padding: 20px; background: rgba(5,9,20,.3); border-radius: 12px; border: 1px solid var(--line);">
                    <h4 style="color: var(--text); margin-bottom: 15px;">Actions en masse</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <select class="form-control" style="width: auto; min-width: 200px;">
                            <option value="">Sélectionner une action...</option>
                            <option value="approve">Approuver les clients sélectionnés</option>
                            <option value="activate">Activer les abonnements</option>
                            <option value="email">Envoyer un email</option>
                            <option value="delete">Supprimer les clients</option>
                        </select>
                        <button class="btn btn-primary">Appliquer</button>
                        <button class="btn btn-secondary" id="clearSelection">Effacer la sélection</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sélection tous/none
            const selectAll = document.getElementById('selectAll');
            const clientCheckboxes = document.querySelectorAll('.client-checkbox');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    clientCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // Effacer la sélection
            const clearSelection = document.getElementById('clearSelection');
            if (clearSelection) {
                clearSelection.addEventListener('click', function() {
                    clientCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    selectAll.checked = false;
                });
            }

            // Gestion des filtres
            const filterBtn = document.querySelector('button:contains("Filtrer")');
            if (filterBtn) {
                filterBtn.addEventListener('click', function() {
                    // Implémente la logique de filtrage ici
                    alert('Filtrage appliqué');
                });
            }
        });
    </script>
@endpush
