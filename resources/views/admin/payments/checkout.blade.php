@extends('layouts.app')

@section('title', 'Paiement - Choisir un plan')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Choisir votre plan d'abonnement
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Choisissez le plan qui correspond à vos besoins. Une fois le paiement effectué, votre compte sera activé sous 24h.
                        </div>

                        <div class="row">
                            @foreach($plans as $planKey => $plan)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-{{ $planKey == 'normal' ? 'primary' : ($planKey == 'elite' ? 'warning' : 'secondary') }}">
                                        <div class="card-header text-center py-3">
                                            <h5 class="card-title mb-1">
                                                @if($planKey == 'basic')
                                                    <span class="badge bg-secondary">Basic</span>
                                                @elseif($planKey == 'normal')
                                                    <span class="badge bg-primary">Normal</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Elite</span>
                                                @endif
                                            </h5>
                                            <h3 class="mt-3">{{ $plan['price'] }} FCFA</h3>
                                            <small class="text-muted">/mois</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-4">
                                                <div class="display-6">
                                                    @if($plan['accounts'] == 'Illimité')
                                                        <i class="fas fa-infinity text-primary"></i>
                                                    @else
                                                        <i class="fas fa-user-friends"></i>
                                                    @endif
                                                </div>
                                                <p class="mb-0">
                                                    <strong>{{ $plan['accounts'] }}</strong> compte(s) MT5
                                                </p>
                                            </div>

                                            <ul class="list-unstyled">
                                                @foreach($plan['features'] as $feature)
                                                    <li class="mb-2">
                                                        <i class="fas fa-check text-success me-2"></i>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                            <form action="{{ route('client.payments.process') }}" method="POST" enctype="multipart/form-data" id="paymentForm{{ $planKey }}">
                                                @csrf
                                                <input type="hidden" name="plan" value="{{ $planKey }}">

                                                <button type="button" class="btn btn-{{ $planKey == 'normal' ? 'primary' : ($planKey == 'elite' ? 'warning' : 'secondary') }} w-100"
                                                        data-bs-toggle="modal" data-bs-target="#paymentModal{{ $planKey }}">
                                                    <i class="fas fa-credit-card me-2"></i>Choisir ce plan
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals pour chaque plan -->
    @foreach($plans as $planKey => $plan)
        <div class="modal fade" id="paymentModal{{ $planKey }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Paiement - Plan {{ ucfirst($planKey) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('client.payments.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $planKey }}">

                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Montant à payer : <strong>{{ $plan['price'] }} FCFA</strong>
                            </div>

                            <div class="mb-3">
                                <label for="method{{ $planKey }}" class="form-label">Méthode de paiement *</label>
                                <select class="form-select" id="method{{ $planKey }}" name="method" required>
                                    <option value="">Choisissez une méthode</option>
                                    <option value="wave">Wave</option>
                                    <option value="orange">Orange Money</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="transaction_id{{ $planKey }}" class="form-label">
                                    Numéro de transaction (optionnel)
                                </label>
                                <input type="text" class="form-control" id="transaction_id{{ $planKey }}"
                                       name="transaction_id" placeholder="Ex: 123456789">
                                <small class="text-muted">Fournissez le numéro de transaction si disponible</small>
                            </div>

                            <div class="mb-3">
                                <label for="proof{{ $planKey }}" class="form-label">
                                    Preuve de paiement *
                                    <small class="text-muted">(Capture d'écran ou reçu)</small>
                                </label>
                                <input type="file" class="form-control" id="proof{{ $planKey }}"
                                       name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">Formats acceptés : JPG, PNG, PDF (max 5MB)</small>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Instructions :</h6>
                                <ol class="mb-0">
                                    <li>Effectuez le paiement de {{ $plan['price'] }} FCFA</li>
                                    <li>Prenez une capture d'écran ou photo du reçu</li>
                                    <li>Téléchargez la preuve ci-dessus</li>
                                    <li>Votre compte sera activé sous 24h après vérification</li>
                                </ol>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Soumettre le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        // Validation des fichiers
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');

            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

                    if (file) {
                        if (file.size > maxSize) {
                            alert('Le fichier est trop volumineux (max 5MB)');
                            this.value = '';
                        } else if (!allowedTypes.includes(file.type)) {
                            alert('Format de fichier non supporté. Utilisez JPG, PNG ou PDF.');
                            this.value = '';
                        }
                    }
                });
            });
        });
    </script>
@endsection
