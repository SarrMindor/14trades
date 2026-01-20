@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs - Admin')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1 class="card-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Gestion des Utilisateurs
                    </h1>
                    <div class="admin-stats">
                    <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success); padding: 8px 16px; margin-right: 10px;">
                        <i class="bi bi-person-check me-1"></i> {{ $activeUsers }} Actifs
                    </span>
                        <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error); padding: 8px 16px;">
                        <i class="bi bi-person-x me-1"></i> {{ $inactiveUsers }} Inactifs
                    </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Actions rapides -->
                <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-person-plus me-1"></i> Nouvel Utilisateur
                    </button>

                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Administrateurs</option>
                        <option value="client">Clients</option>
                        <option value="moderator">Modérateurs</option>
                    </select>

                    <select class="form-control" style="width: auto; min-width: 180px;">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Inactifs</option>
                        <option value="suspended">Suspendus</option>
                    </select>

                    <input type="text" class="form-control" placeholder="Rechercher un utilisateur..." style="flex: 1; min-width: 200px;">

                    <button class="btn btn-secondary">
                        <i class="bi bi-search me-1"></i> Rechercher
                    </button>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="table-container">
                    <table class="table">
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
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar" style="width: 40px; height: 40px; font-size: 16px;
                                            background: {{ $user->role === 'admin' ? 'linear-gradient(135deg, #3498db, #2980b9)' : 'linear-gradient(135deg, var(--gold), #ffed4e)' }};">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text);">{{ $user->name }}</div>
                                            <div style="font-size: 12px; color: var(--muted);">
                                                ID: {{ $user->id }} • Inscrit le {{ $user->created_at->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge" style="background: rgba(52,152,219,.2); color: #3498db;
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-shield-check me-1"></i> Administrateur
                                    </span>
                                    @elseif($user->role === 'moderator')
                                        <span class="badge" style="background: rgba(155,89,182,.2); color: #9b59b6;
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-shield me-1"></i> Modérateur
                                    </span>
                                    @else
                                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-person me-1"></i> Client
                                    </span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'Non renseigné' }}</td>
                                <td>
                                    @if($user->last_login_at)
                                        <div style="color: var(--text); font-weight: 500;">
                                            {{ $user->last_login_at->format('d/m/Y') }}
                                        </div>
                                        <div style="font-size: 12px; color: var(--muted);">
                                            {{ $user->last_login_at->format('H:i') }}
                                        </div>
                                    @else
                                        <span style="color: var(--muted); font-style: italic;">Jamais</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_approved)
                                        @if($user->subscription_status === 'active')
                                            <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-check-circle me-1"></i> Actif
                                        </span>
                                        @elseif($user->subscription_status === 'pending')
                                            <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-clock me-1"></i> En attente
                                        </span>
                                        @else
                                            <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error);
                                                padding: 6px 12px; border-radius: 12px;">
                                            <i class="bi bi-x-circle me-1"></i> Inactif
                                        </span>
                                        @endif
                                    @else
                                        <span class="badge" style="background: rgba(241,196,15,.2); color: #f1c40f;
                                            padding: 6px 12px; border-radius: 12px;">
                                        <i class="bi bi-person-x me-1"></i> Non approuvé
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Éditer -->
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Voir -->
                                        <button type="button" class="btn btn-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal{{ $user->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Menu actions -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu" style="background: var(--panel); border: 1px solid var(--line);">
                                                @if(!$user->is_approved)
                                                    <li>
                                                        <form action="{{ route('admin.clients.approve', $user->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item" style="color: var(--success);">
                                                                <i class="bi bi-check-circle me-2"></i> Approuver
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if($user->is_approved)
                                                    <li>
                                                        <form action="#" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="is_approved" value="0">
                                                            <button type="submit" class="dropdown-item" style="color: #f1c40f;">
                                                                <i class="bi bi-person-x me-2"></i> Désapprouver
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="bi bi-key me-2"></i> Réinitialiser MDP
                                                    </a>
                                                </li>

                                                <li><hr class="dropdown-divider"></li>

                                                @if($user->role !== 'admin')
                                                    <li>
                                                        <form action="#" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="role" value="admin">
                                                            <button type="submit" class="dropdown-item" style="color: #3498db;">
                                                                <i class="bi bi-shield-check me-2"></i> Promouvoir Admin
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if($user->role === 'admin')
                                                    <li>
                                                        <form action="#" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="role" value="client">
                                                            <button type="submit" class="dropdown-item" style="color: var(--gold);">
                                                                <i class="bi bi-person me-2"></i> Rétrograder Client
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                <li><hr class="dropdown-divider"></li>

                                                <li>
                                                    <form action="#" method="POST" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?')">
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

                            <!-- Modal Voir Utilisateur -->
                            <div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content" style="background: var(--panel); color: var(--text);">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Profil Utilisateur</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="text-center mb-4">
                                                        <div class="user-avatar" style="width: 100px; height: 100px; font-size: 40px;
                                                            margin: 0 auto; background: {{ $user->role === 'admin' ? 'linear-gradient(135deg, #3498db, #2980b9)' : 'linear-gradient(135deg, var(--gold), #ffed4e)' }};">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                        <h5 class="mt-3">{{ $user->name }}</h5>
                                                        <div style="color: var(--muted);">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-4">
                                                        <h6>Informations Générales</h6>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">ID</div>
                                                                    <div style="color: var(--text);">{{ $user->id }}</div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Rôle</div>
                                                                    <div>
                                                                        @if($user->role === 'admin')
                                                                            <span style="color: #3498db;">
                                                                            <i class="bi bi-shield-check"></i> Administrateur
                                                                        </span>
                                                                        @else
                                                                            <span style="color: var(--gold);">
                                                                            <i class="bi bi-person"></i> Client
                                                                        </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Téléphone</div>
                                                                    <div style="color: var(--text);">{{ $user->phone ?? 'Non renseigné' }}</div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Dernière connexion</div>
                                                                    <div style="color: var(--text);">
                                                                        {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <h6>Statut & Abonnement</h6>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Statut Compte</div>
                                                                    <div>
                                                                        @if($user->is_approved)
                                                                            <span style="color: var(--success);">
                                                                            <i class="bi bi-check-circle"></i> Approuvé
                                                                        </span>
                                                                        @else
                                                                            <span style="color: #f1c40f;">
                                                                            <i class="bi bi-person-x"></i> Non approuvé
                                                                        </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Abonnement</div>
                                                                    <div>
                                                                        @if($user->subscription_status === 'active')
                                                                            <span style="color: var(--success);">
                                                                            <i class="bi bi-lightning"></i> Actif
                                                                        </span>
                                                                        @elseif($user->subscription_status === 'pending')
                                                                            <span style="color: var(--gold);">
                                                                            <i class="bi bi-clock"></i> En attente
                                                                        </span>
                                                                        @else
                                                                            <span style="color: var(--error);">
                                                                            <i class="bi bi-x-circle"></i> Inactif
                                                                        </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Plan</div>
                                                                    <div style="color: var(--text); text-transform: uppercase;">
                                                                        {{ $user->plan ?? 'Aucun' }}
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Expiration</div>
                                                                    <div style="color: var(--text);">
                                                                        {{ $user->subscription_ends_at?->format('d/m/Y') ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h6>Activité Récente</h6>
                                                        <div style="background: rgba(5,9,20,.3); padding: 15px; border-radius: 8px;">
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">Date d'inscription</div>
                                                                    <div style="color: var(--text);">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <div style="color: var(--muted); font-size: 13px;">IP de connexion</div>
                                                                    <div style="color: var(--text); font-family: monospace;">
                                                                        {{ $user->last_login_ip ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">Éditer</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color: var(--muted);">
                                    <i class="bi bi-people" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div style="color: var(--muted);">
                            Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} utilisateurs
                        </div>
                        <nav>
                            <ul class="pagination">
                                @if($users->onFirstPage())
                                    <li class="page-item disabled">
                                <span class="page-link" style="background: var(--panel); border-color: var(--line); color: var(--muted);">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->previousPageUrl() }}"
                                           style="background: var(--panel); border-color: var(--line); color: var(--text);">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach(range(1, $users->lastPage()) as $page)
                                    @if($page == $users->currentPage())
                                        <li class="page-item active">
                                    <span class="page-link" style="background: var(--gold); border-color: var(--gold);">
                                        {{ $page }}
                                    </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->url($page) }}"
                                               style="background: var(--panel); border-color: var(--line); color: var(--text);">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach

                                @if($users->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->nextPageUrl() }}"
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

                <!-- Statistiques utilisateurs -->
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title" style="font-size: 16px;">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    Répartition par Rôle
                                </h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                    <!-- Diagramme circulaire -->
                                    <div style="width: 150px; height: 150px; border-radius: 50%;
                                        background: conic-gradient(
                                            #3498db 0% {{ ($users->where('role', 'admin')->count() / $users->count() * 100) ?? 10 }}%,
                                            #9b59b6 {{ ($users->where('role', 'admin')->count() / $users->count() * 100) ?? 10 }}% {{ (($users->where('role', 'admin')->count() + $users->where('role', 'moderator')->count()) / $users->count() * 100) ?? 20 }}%,
                                            var(--gold) {{ (($users->where('role', 'admin')->count() + $users->where('role', 'moderator')->count()) / $users->count() * 100) ?? 20 }}% 100%
                                        ); position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                            width: 80px; height: 80px; background: var(--panel); border-radius: 50%;">
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                                    <div style="text-align: center;">
                                        <div style="color: #3498db; font-size: 20px; font-weight: 700;">
                                            {{ $users->where('role', 'admin')->count() }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Admins</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="color: #9b59b6; font-size: 20px; font-weight: 700;">
                                            {{ $users->where('role', 'moderator')->count() }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Modérateurs</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="color: var(--gold); font-size: 20px; font-weight: 700;">
                                            {{ $users->where('role', 'client')->count() }}
                                        </div>
                                        <div style="color: var(--muted); font-size: 12px;">Clients</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title" style="font-size: 16px;">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Inscriptions (30 derniers jours)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; padding: 20px;">
                                    <!-- Graphique d'inscriptions -->
                                    <div style="display: flex; align-items: flex-end; height: 150px; gap: 10px;">
                                        @for($i = 29; $i >= 0; $i--)
                                            @php
                                                $date = now()->subDays($i);
                                                $count = rand(0, 5);
                                            @endphp
                                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                                                <div style="width: 10px; background: linear-gradient(to top, var(--gold), #ffed4e);
                                                    height: {{ $count * 20 }}px; border-radius: 2px 2px 0 0;">
                                                </div>
                                                @if($i % 5 === 0)
                                                    <div style="margin-top: 10px; color: var(--muted); font-size: 10px;">
                                                        {{ $date->format('d/m') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Créer Utilisateur -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--panel); color: var(--text);">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select" required>
                                <option value="client">Client</option>
                                <option value="moderator">Modérateur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut</label>
                                    <select name="is_approved" class="form-select">
                                        <option value="1">Approuvé</option>
                                        <option value="0">En attente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de la recherche
            const searchBtn = document.querySelector('button:contains("Rechercher")');
            if (searchBtn) {
                searchBtn.addEventListener('click', function() {
                    const searchTerm = document.querySelector('input[placeholder*="Rechercher"]').value;
                    const roleFilter = document.querySelector('select:first-of-type').value;
                    const statusFilter = document.querySelector('select:nth-of-type(2)').value;

                    // Implémenter la logique de recherche ici
                    alert(`Recherche: ${searchTerm}, Rôle: ${roleFilter}, Statut: ${statusFilter}`);
                });
            }
        });
    </script>
@endpush
