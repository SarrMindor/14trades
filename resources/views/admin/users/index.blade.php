@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs - Admin')

@push('styles')
    <!-- Ajoutez le CSS ci-dessus ici -->
@endpush

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card card-admin-users">
            <div class="card-header card-header-admin">
                <div class="header-content-admin">
                    <h1 class="card-title-admin">
                        <i class="bi bi-person-badge"></i>
                        Gestion des Utilisateurs
                    </h1>
                    <div class="admin-stats">
                        <span class="admin-stat-badge active">
                            <i class="bi bi-person-check"></i> {{ $activeUsers }} Actifs
                        </span>
                        <span class="admin-stat-badge inactive">
                            <i class="bi bi-person-x"></i> {{ $inactiveUsers }} Inactifs
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Actions rapides -->
                <div class="admin-actions">
                    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-person-plus"></i> Nouvel Utilisateur
                    </button>

                    <div class="admin-filters">
                        <select class="form-control form-control-admin">
                            <option value="">Tous les rôles</option>
                            <option value="admin">Administrateurs</option>
                            <option value="client">Clients</option>
                            <option value="moderator">Modérateurs</option>
                        </select>

                        <select class="form-control form-control-admin">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actifs</option>
                            <option value="inactive">Inactifs</option>
                            <option value="suspended">Suspendus</option>
                        </select>

                        <input type="text" class="form-control form-control-admin"
                               placeholder="Rechercher un utilisateur...">

                        <button class="btn btn-admin-search">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="table-container table-container-admin">
                    <table class="table table-admin">
                        <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Dernière connexion</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-cell
                                                @if($user->role === 'admin')user-avatar-admin-bg
                                                @elseif($user->role === 'moderator')user-avatar-moderator-bg
                                                @else user-avatar-client-bg
                                                @endif">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="user-info-cell">
                                            <div class="user-name-cell">{{ $user->name }}</div>
                                            <div class="user-meta-cell">
                                                <span class="user-id">ID: {{ $user->id }}</span>
                                                <span class="user-date">Inscrit le {{ $user->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                        <span class="role-badge {{ $user->role }}">
                                            <i class="bi bi-{{ $user->role === 'admin' ? 'shield-check' : ($user->role === 'moderator' ? 'shield' : 'person') }}"></i>
                                            {{ ucfirst($user->role) }}
                                        </span>
                                </td>
                                <td>
                                    <div class="email-cell">{{ $user->email }}</div>
                                </td>
                                <td>
                                    <div class="phone-cell">{{ $user->phone ?? 'Non renseigné' }}</div>
                                </td>
                                <td class="login-cell">
                                    @if($user->last_login_at)
                                        <div class="login-date-cell">{{ $user->last_login_at->format('d/m/Y') }}</div>
                                        <div class="login-time-cell">{{ $user->last_login_at->format('H:i') }}</div>
                                    @else
                                        <span class="login-never">Jamais</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'inactive';
                                        $statusIcon = 'x-circle';
                                        $statusText = 'Inactif';

                                        if($user->is_approved) {
                                            if($user->subscription_status === 'active') {
                                                $statusClass = 'active';
                                                $statusIcon = 'check-circle';
                                                $statusText = 'Actif';
                                            } elseif($user->subscription_status === 'pending') {
                                                $statusClass = 'pending';
                                                $statusIcon = 'clock';
                                                $statusText = 'En attente';
                                            }
                                        } else {
                                            $statusClass = 'unapproved';
                                            $statusIcon = 'person-x';
                                            $statusText = 'Non approuvé';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                            <i class="bi bi-{{ $statusIcon }}"></i>
                                            {{ $statusText }}
                                        </span>
                                </td>
                                <td class="actions-cell">
                                    <div class="actions-group">
                                        <!-- Éditer -->
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                           class="btn btn-action btn-edit"
                                           data-tooltip="Éditer">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Voir -->
                                        <button type="button"
                                                class="btn btn-action btn-view"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal{{ $user->id }}"
                                                data-tooltip="Voir">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Menu actions -->
                                        <div class="dropdown dropdown-actions">
                                            <button class="btn btn-action btn-settings dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    data-tooltip="Plus d'actions">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <!-- Contenu du dropdown... -->
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Modal Voir Utilisateur... -->
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-people empty-icon"></i>
                                        <p class="empty-text">Aucun utilisateur trouvé</p>
                                        <p class="text-muted">Commencez par créer un nouvel utilisateur</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="pagination-container-admin">
                        <div class="pagination-info">
                            Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} utilisateurs
                        </div>
                        <nav>
                            <ul class="pagination pagination-admin">
                                <!-- Liens de pagination... -->
                            </ul>
                        </nav>
                    </div>
                @endif

                <!-- Statistiques utilisateurs -->
                <div class="stats-section-admin">
                    <div class="stats-grid-admin">
                        <!-- Carte répartition par rôle... -->
                        <!-- Carte inscriptions... -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Créer Utilisateur -->
    <div class="modal fade" id="createUserModal">
        <div class="modal-dialog">
            <div class="modal-content modal-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title-admin">
                        <i class="bi bi-person-plus"></i>
                        Créer un Nouvel Utilisateur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="#" method="POST" class="form-create-user">
                    @csrf
                    <div class="modal-body modal-body-admin">
                        <!-- Formulaire... -->
                    </div>
                    <div class="modal-footer modal-footer-admin">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer-scripts')
    <script>
        // JavaScript pour les interactions
    </script>
@endpush
