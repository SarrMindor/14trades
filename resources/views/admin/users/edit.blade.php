@extends('layouts.app')

@section('title', 'Éditer Utilisateur - Admin')

@section('content')
    <div class="container-custom">
        <div class="card card-admin-edit">
            <div class="card-header">
                <div class="header-content">
                    <h1 class="card-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        Éditer Utilisateur
                    </h1>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informations de l'utilisateur -->
                <div class="user-header mb-4">
                    <div class="user-info-banner">
                        <div class="user-avatar-large user-avatar-{{ $user->role }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="user-details">
                            <h2>{{ $user->name }}</h2>
                            <div class="user-meta">
                                <span class="badge badge-{{ $user->role }}">
                                    <i class="bi bi-{{ $user->role === 'admin' ? 'shield-check' : 'person' }} me-1"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                                <span class="text-muted">
                                    <i class="bi bi-envelope me-1"></i> {{ $user->email }}
                                </span>
                                <span class="text-muted">
                                    <i class="bi bi-calendar me-1"></i> Inscrit le {{ $user->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'édition -->
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h4 class="section-title">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Informations personnelles
                                </h4>

                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Laissez vide pour ne pas modifier">
                                    <small class="form-text text-muted">Minimum 8 caractères</small>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirmation mot de passe</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-section">
                                <h4 class="section-title">
                                    <i class="bi bi-gear me-2"></i>
                                    Paramètres du compte
                                </h4>

                                <div class="mb-3">
                                    <label class="form-label">Rôle *</label>
                                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="client" {{ old('role', $user->role) == 'client' ? 'selected' : '' }}>
                                            Client
                                        </option>
                                        <option value="moderator" {{ old('role', $user->role) == 'moderator' ? 'selected' : '' }}>
                                            Modérateur
                                        </option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                            Administrateur
                                        </option>
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Statut du compte</label>
                                    <select name="is_approved" class="form-select @error('is_approved') is-invalid @enderror">
                                        <option value="1" {{ old('is_approved', $user->is_approved) ? 'selected' : '' }}>
                                            Compte approuvé
                                        </option>
                                        <option value="0" {{ !old('is_approved', $user->is_approved) ? 'selected' : '' }}>
                                            Compte en attente
                                        </option>
                                    </select>
                                    @error('is_approved')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Statut d'abonnement</label>
                                    <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror">
                                        <option value="active" {{ old('subscription_status', $user->subscription_status) == 'active' ? 'selected' : '' }}>
                                            Actif
                                        </option>
                                        <option value="pending" {{ old('subscription_status', $user->subscription_status) == 'pending' ? 'selected' : '' }}>
                                            En attente
                                        </option>
                                        <option value="inactive" {{ old('subscription_status', $user->subscription_status) == 'inactive' ? 'selected' : '' }}>
                                            Inactif
                                        </option>
                                        <option value="suspended" {{ old('subscription_status', $user->subscription_status) == 'suspended' ? 'selected' : '' }}>
                                            Suspendu
                                        </option>
                                    </select>
                                    @error('subscription_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Plan</label>
                                    <select name="plan" class="form-select @error('plan') is-invalid @enderror">
                                        <option value="">Aucun plan</option>
                                        <option value="starter" {{ old('plan', $user->plan) == 'starter' ? 'selected' : '' }}>
                                            Starter
                                        </option>
                                        <option value="pro" {{ old('plan', $user->plan) == 'pro' ? 'selected' : '' }}>
                                            Pro
                                        </option>
                                        <option value="elite" {{ old('plan', $user->plan) == 'elite' ? 'selected' : '' }}>
                                            Elite
                                        </option>
                                    </select>
                                    @error('plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date d'expiration d'abonnement</label>
                                    <input type="date" name="subscription_ends_at"
                                           class="form-control @error('subscription_ends_at') is-invalid @enderror"
                                           value="{{ old('subscription_ends_at', $user->subscription_ends_at ? $user->subscription_ends_at->format('Y-m-d') : '') }}">
                                    @error('subscription_ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-section mt-4">
                                <h4 class="section-title">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    Sécurité
                                </h4>

                                <div class="form-check mb-3">
                                    <input type="checkbox" name="force_password_reset" id="force_password_reset"
                                           class="form-check-input" value="1">
                                    <label class="form-check-label" for="force_password_reset">
                                        Forcer la réinitialisation du mot de passe à la prochaine connexion
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active"
                                           class="form-check-input" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Compte activé
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions mt-5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash me-1"></i> Supprimer
                                </button>
                            </div>
                            <div class="d-flex gap-3">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content modal-admin">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ $user->name }}</strong> ?</p>
                    <p class="text-muted">Toutes les données associées seront également supprimées.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Styles spécifiques à la page d'édition */

        .card-admin-edit {
            margin-bottom: 30px;
        }

        .user-header {
            padding: 20px;
            background: rgba(255,215,0,.05);
            border-radius: var(--radius);
            border: 1px solid rgba(255,215,0,.2);
        }

        .user-info-banner {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 32px;
            flex-shrink: 0;
        }

        .user-details {
            flex: 1;
        }

        .user-details h2 {
            color: var(--text);
            margin-bottom: 8px;
            font-size: 24px;
        }

        .user-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .text-muted {
            color: var(--muted) !important;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }

        /* Sections de formulaire */
        .form-section {
            background: rgba(18,27,47,.6);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 20px;
        }

        .section-title {
            color: var(--gold);
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
        }

        /* Champs de formulaire */
        .form-label {
            color: var(--muted);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-select {
            background: rgba(5,9,20,.6);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text);
            padding: 12px 16px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(5,9,20,.8);
            border-color: rgba(255,215,0,.5);
            box-shadow: 0 0 0 3px rgba(255,215,0,.1);
            color: var(--text);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: rgba(231,76,60,.5);
            background: rgba(231,76,60,.1);
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 13px;
            margin-top: 5px;
        }

        .form-text {
            color: var(--muted);
            font-size: 13px;
            margin-top: 5px;
        }

        /* Checkboxes */
        .form-check-input {
            background-color: rgba(5,9,20,.6);
            border: 1px solid var(--line);
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--gold);
            border-color: var(--gold);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(255,215,0,.25);
            border-color: var(--gold);
        }

        .form-check-label {
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }

        /* Actions du formulaire */
        .form-actions {
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        /* Modal de suppression */
        .modal-admin .alert-danger {
            background: rgba(231,76,60,.1);
            border-color: rgba(231,76,60,.3);
            color: var(--error);
            border-radius: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-info-banner {
                flex-direction: column;
                text-align: center;
            }

            .user-meta {
                justify-content: center;
            }

            .form-actions .d-flex {
                flex-direction: column;
                gap: 15px;
            }

            .form-actions .d-flex > div {
                width: 100%;
                text-align: center;
            }

            .form-actions .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 576px) {
            .form-section {
                padding: 16px;
            }

            .section-title {
                font-size: 16px;
            }
        }
    </style>
@endpush

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation en temps réel
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select');

            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateField(this);
                    }
                });
            });

            function validateField(field) {
                if (field.value.trim() === '' && field.required) {
                    field.classList.add('is-invalid');
                    if (!field.nextElementSibling?.classList.contains('invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Ce champ est requis';
                        field.parentNode.appendChild(errorDiv);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                }
            }

            // Confirmation avant suppression
            const deleteBtn = document.querySelector('.btn-danger[data-bs-target="#deleteModal"]');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
@endpush
