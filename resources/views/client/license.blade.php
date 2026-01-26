@extends('layouts.app')

@section('title', 'Ma Licence - 14TRADES')

@section('content')
    <div class="container-custom">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            Ma Licence 14TRADES PRO
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($license)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">Statut de la Licence</label>
                                        <div class="alert alert-{{ $license->isValid() ? 'success' : 'danger' }}">
                                            <i class="bi bi-{{ $license->isValid() ? 'check-circle' : 'x-circle' }} me-2"></i>
                                            <strong>{{ $license->isValid() ? 'ACTIVE' : 'INACTIVE' }}</strong>
                                            @if($license->isExpiringSoon() && $license->isValid())
                                                <div class="mt-2">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Expire dans {{ $license->expires_at->diffInDays(now()) }} jours
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Plan</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                   value="{{ strtoupper($license->plan) }} - {{ $license->plan == 'basic' ? '$49/mois' : ($license->plan == 'normal' ? '$99/mois' : '$199/mois') }}"
                                                   readonly>
                                            @if($license->plan !== 'elite')
                                                <a href="{{ route('client.payment') }}?plan=upgrade&current={{ $license->plan }}"
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-arrow-up-circle"></i> Upgrader
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Multiplicateur de Lot</label>
                                        <input type="text" class="form-control"
                                               value="x{{ $license->getLotMultiplier() }}"
                                               readonly>
                                        <small class="text-muted">
                                            Votre EA utilisera ce multiplicateur pour calculer les tailles de lot
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Compte MT5</label>
                                        <input type="text" class="form-control" value="{{ $license->mt5_account }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Serveur</label>
                                        <input type="text" class="form-control" value="{{ $license->server }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Date d'Expiration</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                   value="{{ $license->expires_at->format('d/m/Y') }} ({{ $license->expires_at->diffForHumans() }})"
                                                   readonly>
                                            <a href="{{ route('client.payment') }}?action=renew&license={{ $license->id }}"
                                               class="btn btn-outline-success">
                                                <i class="bi bi-calendar-plus"></i> Renouveler
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Dernière Validation</label>
                                        <input type="text" class="form-control"
                                               value="{{ $license->last_validation ? $license->last_validation->format('d/m/Y H:i') : 'Jamais' }}"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Information pour l'EA -->
                            <div class="alert alert-info mt-4">
                                <h5><i class="bi bi-info-circle me-2"></i>Configuration de l'EA</h5>
                                <p class="mb-2">URL de validation :</p>
                                <code class="bg-dark text-light p-2 rounded d-block mb-3">{{ url('/api/license/validate') }}</code>

                                <p class="mb-2">Vos paramètres :</p>
                                <pre class="bg-dark text-light p-3 rounded">
Account: {{ $license->mt5_account }}
Server: {{ $license->server }}
HWID: [Auto-détecté par l'EA]</pre>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-shield-slash display-4 text-muted mb-3"></i>
                                <h4>Aucune licence active</h4>
                                <p class="text-muted mb-4">
                                    Pour utiliser l'EA 14TRADES PRO, vous avez besoin d'une licence active.
                                </p>
                                <a href="{{ route('client.payment') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-cart me-1"></i> Acheter une licence
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Téléchargement de l'EA -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="bi bi-download display-4 text-primary mb-3"></i>
                        <h5>Télécharger l'EA</h5>
                        <p class="text-muted small mb-3">
                            Version 2.0 avec protection licence intégrée
                        </p>
                        @if($license && $license->isValid())
                            <a href="{{ route('client.download.ea') }}" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-file-earmark-zip me-1"></i> Télécharger
                            </a>
                            <small class="text-muted">Taille: 4.2 MB | Version: 2.0.1</small>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Licence requise
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Historique des licences -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Historique des Licences</h6>
                    </div>
                    <div class="card-body">
                        @forelse($licenses as $lic)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ strtoupper($lic->plan) }}</div>
                                    <small class="text-muted">{{ $lic->mt5_account }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="small">{{ $lic->expires_at->format('d/m/Y') }}</div>
                                    <span class="badge badge-sm bg-{{ $lic->is_active ? 'success' : 'secondary' }}">
                                {{ $lic->is_active ? 'Active' : 'Inactive' }}
                            </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3">
                                <i class="bi bi-clock-history text-muted"></i>
                                <p class="text-muted small mb-0">Aucun historique</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
