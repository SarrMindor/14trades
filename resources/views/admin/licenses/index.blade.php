@extends('layouts.app')

@section('title', 'Gestion des Licences - Admin')

@section('content')
    <div class="container-custom">
        <div class="card card-licenses">
            <div class="card-header card-header-licenses">
                <div class="header-content">
                    <h1 class="license-card-title">
                        <i class="bi bi-shield-lock"></i>
                        Gestion des Licences
                    </h1>
                    <div class="license-info">
                    <span class="badge badge-primary">
                        <i class="bi bi-link-45deg me-1"></i>
                        API: {{ url('/api/license/validate') }}
                    </span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- ... (même contenu que précédemment) ... -->

                <!-- Liste des licences -->
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Client</th>
                            <th>Compte MT5</th>
                            <th>Serveur</th>
                            <th>Plan</th>
                            <th>Expiration</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($licenses as $license)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                            {{ substr($license->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div>{{ $license->user->name }}</div>
                                            <small class="text-muted">{{ $license->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $license->mt5_account }}</code></td>
                                <td>{{ $license->server }}</td>
                                <td>
                                <span class="badge badge-{{ $license->plan === 'elite' ? 'danger' : ($license->plan === 'normal' ? 'warning' : 'info') }}">
                                    {{ strtoupper($license->plan) }}
                                </span>
                                </td>
                                <td>
                                    {{ $license->expires_at->format('d/m/Y') }}
                                    <div class="small text-muted">{{ $license->expires_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if($license->is_active && $license->expires_at->isFuture())
                                        <span class="badge badge-success">Active</span>
                                    @elseif($license->expires_at->isPast())
                                        <span class="badge badge-danger">Expirée</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deactivateLicense({{ $license->id }})">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-shield-slash display-4 text-muted mb-3"></i>
                                    <p>Aucune licence trouvée</p>
                                    <a href="{{ route('admin.licenses.create') }}" class="btn btn-primary">
                                        Créer une licence
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $licenses->links() }}
            </div>
        </div>
    </div>
@endsection
