@extends('layouts.app')

@section('title', 'Ajouter un compte MT5')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Ajouter un compte MT5
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Plan {{ strtoupper($user->plan) }}:</strong>
                            Vous avez utilisé {{ $user->mt5Accounts->count() }} sur
                            @if($user->plan == 'basic')
                                1 compte autorisé.
                            @elseif($user->plan == 'normal')
                                3 comptes autorisés.
                            @else
                                comptes illimités.
                            @endif
                        </div>

                        <form action="{{ route('client.accounts.store') }}" method="POST" id="addAccountForm">
                            @csrf

                            <div class="mb-3">
                                <label for="account_number" class="form-label">Numéro de compte MT5 *</label>
                                <input type="number" class="form-control" id="account_number"
                                       name="account_number" required
                                       placeholder="Ex: 12345678">
                                <div class="form-text">Le numéro de votre compte MT5</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe investisseur *</label>
                                <input type="password" class="form-control" id="password"
                                       name="password" required
                                       placeholder="Mot de passe investisseur">
                                <div class="form-text">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Utilisez le mot de passe <strong>investisseur</strong>, pas le mot de passe principal.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="server" class="form-label">Serveur *</label>
                                <select class="form-select" id="server" name="server" required>
                                    <option value="">Sélectionnez un serveur</option>
                                    <option value="Default" selected>Default</option>
                                    <option value="Live">Live</option>
                                    <option value="Demo">Demo</option>
                                    <option value="ICMarkets">IC Markets</option>
                                    <option value="XM">XM</option>
                                    <option value="Exness">Exness</option>
                                    <option value="FxPro">FxPro</option>
                                    <option value="Pepperstone">Pepperstone</option>
                                </select>
                                <div class="form-text">Le serveur sur lequel votre compte est enregistré</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nom (optionnel)</label>
                                    <input type="text" class="form-control" id="name"
                                           name="name" placeholder="Ex: Compte Principal">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email (optionnel)</label>
                                    <input type="email" class="form-control" id="email"
                                           name="email" placeholder="email@example.com">
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <h6><i class="fas fa-shield-alt me-2"></i>Sécurité des données</h6>
                                <p class="mb-0">
                                    Votre mot de passe est crypté de manière sécurisée.
                                    Nous n'utilisons que le mot de passe investisseur pour la synchronisation des données.
                                </p>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('client.accounts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter le compte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addAccountForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Vérification en cours...';
            });
        });
    </script>
@endsection
