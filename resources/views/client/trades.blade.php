@extends('layouts.app')

@section('title', 'Mes Trades')

@section('content')
    <div class="container-custom">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mes Trades</h1>
            </div>
            <div class="card-body">
                <p style="color: var(--muted);">
                    Cette fonctionnalité sera disponible prochainement. Vous pourrez voir l'historique de tous vos trades ici.
                </p>

                <!-- Tableau factice -->
                <div class="table-container mt-4">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Symbol</th>
                            <th>Type</th>
                            <th>Volume</th>
                            <th>Profit</th>
                            <th>Statut</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--muted2);">
                                Aucun trade enregistré pour le moment
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('client.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Retour au dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
