@extends('layouts.app')

@section('title', 'Mes Comptes MT5')

@section('content')
    <div class="container-custom">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1 class="card-title">Mes Comptes MT5</h1>
                <a href="{{ route('client.accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter un compte
                </a>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($accounts->count() === 0)
                    <div class="text-center py-5">
                        <i class="bi bi-wallet2" style="font-size:48px;color:var(--muted2)"></i>
                        <p class="mt-3 text-muted">Aucun compte MT5 ajouté pour le moment.</p>
                    </div>
                @else
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Login MT5</th>
                            <th>Serveur</th>
                            <th>Devise</th>
                            <th>Date d’ajout</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>{{ $account->login }}</td>
                                <td>{{ $account->server }}</td>
                                <td>{{ $account->currency }}</td>
                                <td>{{ $account->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('client.accounts.destroy', $account->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
