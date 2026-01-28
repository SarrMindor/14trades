@extends('layouts.app')

@section('title', 'Ajouter un compte MT5')

@section('content')
    <div class="container-custom">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Ajouter un compte MT5
                </h1>
            </div>

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="addAccountForm" action="{{ route('client.accounts.store') }}" method="POST">
                    @csrf

                    <!-- LOGIN MT5 -->
                    <div class="mb-4">
                        <label for="login" class="form-label">Login MT5 *</label>
                        <input
                            type="text"
                            name="login"
                            id="login"
                            class="form-control"
                            placeholder="Ex: 12345678"
                            required
                            minlength="5"
                            maxlength="20"
                            value="{{ old('login') }}"
                        >
                        <small class="form-text text-muted">
                            Le numéro de compte doit être composé uniquement de chiffres (5-20 caractères).
                        </small>
                    </div>

                    <!-- SERVEUR -->
                    <div class="mb-4">
                        <label for="server" class="form-label">Serveur du broker *</label>
                        <input
                            type="text"
                            name="server"
                            id="server"
                            class="form-control"
                            placeholder="Ex: Exness-MT5Real, ICMarkets-Demo"
                            required
                            value="{{ old('server') }}"
                        >
                        <small class="form-text text-muted">
                            Le nom exact du serveur MT5 fourni par votre broker.
                        </small>
                    </div>

                    <!-- MOT DE PASSE (Optionnel pour certains brokers) -->
                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe MT5 (optionnel)</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Mot de passe d'investisseur"
                            value="{{ old('password') }}"
                        >
                        <small class="form-text text-muted">
                            Requis pour certains brokers. Il s'agit du mot de passe d'investisseur, pas du master password.
                        </small>
                    </div>

                    <!-- TYPE DE COMPTE -->
                    <div class="mb-4">
                        <label class="form-label">Type de compte *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="account_type"
                                       id="real" value="real" {{ old('account_type', 'real') == 'real' ? 'checked' : '' }}>
                                <label class="form-check-label" for="real">
                                    <i class="bi bi-currency-dollar text-success me-1"></i>
                                    Compte Réel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="account_type"
                                       id="demo" value="demo" {{ old('account_type') == 'demo' ? 'checked' : '' }}>
                                <label class="form-check-label" for="demo">
                                    <i class="bi bi-bezier2 text-info me-1"></i>
                                    Compte Démo
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email">Email associé</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="account_number" class="form-label">Numéro du compte MT5</label>
                        <input type="text" name="account_number" id="account_number" class="form-control" required>
                    </div>


                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Validation automatique</strong>
                                <p class="mb-0">Nous vérifierons automatiquement que votre compte MT5 est valide et accessible avant de l'ajouter.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Valider et ajouter le compte
                        </button>
                        <a href="{{ route('client.accounts.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
