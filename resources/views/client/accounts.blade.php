@extends('layouts.app')

@section('title', 'Mes Comptes MT5')

@section('content')
    <div class="container-custom">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mes Comptes MT5</h1>
                <a href="#" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter un compte
                </a>
            </div>
            <div class="card-body">
                <p style="color: var(--muted);">
                    Gérez vos comptes MT5 depuis cette interface.
                </p>

                <div class="text-center py-5">
                    <i class="bi bi-wallet2" style="font-size: 48px; color: var(--muted2); margin-bottom: 20px;"></i>
                    <p style="color: var(--muted); margin-bottom: 20px;">
                        Cette fonctionnalité sera disponible prochainement.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
