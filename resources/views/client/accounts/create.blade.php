@extends('layouts.app')

@section('title', 'Ajouter un compte MT5')

@section('content')
    <div class="container-custom">
        <div class="card">
            <div class="card-header">
                <h1>Ajouter un compte MT5</h1>
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

                <form action="{{ route('client.accounts.store') }}" method="POST">
                    @csrf

                    <!-- LOGIN MT5 -->
                    <div class="mb-3">
                        <label for="login" class="form-label">Login MT5 (Numéro du compte)</label>
                        <input
                            type="text"
                            name="login"
                            id="login"
                            class="form-control"
                            placeholder="Ex: 12345678"
                            required
                        >
                    </div>

                    <!-- SERVEUR -->
                    <div class="mb-3">
                        <label for="server" class="form-label">Serveur du broker</label>
                        <input
                            type="text"
                            name="server"
                            id="server"
                            class="form-control"
                            placeholder="Ex: Exness-MT5Real"
                            required
                        >
                    </div>

                    <!-- DEVISE -->
                    <div class="mb-3">
                        <label for="currency" class="form-label">Devise</label>
                        <select name="currency" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="XOF">XOF</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Ajouter le compte
                        </button>
                        <a href="{{ route('client.accounts.index') }}" class="btn btn-secondary">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
