@extends('layouts.app')

@section('title', 'Gestion des Paiements')

@section('content')
    <div class="container-custom">
        <!-- En-tête -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="bi bi-credit-card me-2"></i>
                    Gestion des Paiements
                </h1>
                <div class="subscription-status">
                    @if(auth()->user()->subscription_status === 'active')
                        <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success); padding: 8px 16px; border-radius: 20px;">
                    <i class="bi bi-check-circle me-1"></i> Abonnement Actif
                </span>
                    @elseif(auth()->user()->subscription_status === 'pending')
                        <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold); padding: 8px 16px; border-radius: 20px;">
                    <i class="bi bi-clock me-1"></i> En Attente
                </span>
                    @else
                        <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error); padding: 8px 16px; border-radius: 20px;">
                    <i class="bi bi-exclamation-circle me-1"></i> Expiré
                </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Info abonnement actuel -->
                <div style="background: rgba(255,215,0,.05); border: 1px solid rgba(255,215,0,.2);
                border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <div>
                            <h3 style="color: var(--gold); margin-bottom: 5px;">Votre Abonnement Actuel</h3>
                            <div style="color: var(--muted);">
                                @if(auth()->user()->subscription_status === 'active')
                                    <p>
                                        Plan <strong style="color: var(--text); text-transform: uppercase;">{{ auth()->user()->plan }}</strong> -
                                        @if(auth()->user()->plan === 'basic')
                                            $49/mois
                                        @elseif(auth()->user()->plan === 'normal')
                                            $99/mois
                                        @elseif(auth()->user()->plan === 'elite')
                                            $199/mois
                                        @endif
                                    </p>
                                    @if(auth()->user()->subscription_ends_at)
                                        <p>Expire le : <strong style="color: var(--text);">
                                                {{ \Carbon\Carbon::parse(auth()->user()->subscription_ends_at)->format('d/m/Y') }}
                                            </strong></p>
                                    @endif
                                @else
                                    <p>Aucun abonnement actif. Souscrivez à un plan pour activer vos fonctionnalités.</p>
                                @endif
                            </div>
                        </div>
                        @if(auth()->user()->subscription_status === 'active' && auth()->user()->subscription_ends_at)
                            <div>
                                @php
                                    $daysRemaining = \Carbon\Carbon::parse(auth()->user()->subscription_ends_at)->diffInDays(now(), false) * -1;
                                @endphp
                                @if($daysRemaining > 0 && $daysRemaining <= 7)
                                    <div style="color: var(--gold); font-weight: 600;">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $daysRemaining }} jour(s) restant(s)
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Plans disponibles -->
                <!-- Au début du fichier payment.blade.php -->
                @php
                    // Si $plans n'est pas défini, le définir avec des valeurs par défaut
                    if (!isset($plans)) {
                        $plans = [
                            [
                                'name' => 'basic',
                                'price' => 49,
                                'features' => ['1 compte MT5', 'Support email', 'Rapports hebdomadaires'],
                            ],
                            [
                                'name' => 'normal',
                                'price' => 99,
                                'features' => ['3 comptes MT5', 'Support prioritaire', 'Rapports quotidiens', 'Signaux additionnels'],
                                'recommended' => true,
                            ],
                            [
                                'name' => 'elite',
                                'price' => 199,
                                'features' => ['Comptes illimités', 'Support 24/7', 'Rapports en temps réel', 'Signaux VIP', 'Mentoring'],
                            ],
                        ];
                    }
                @endphp

                    <!-- Ensuite, dans la section des plans -->
                <h2 style="color: var(--text); margin-bottom: 25px; font-size: 24px;">Choisissez Votre Plan</h2>

                <div class="plans-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    @foreach($plans as $plan)
                        <div class="plan-card"
                             style="background: {{ $plan['recommended'] ?? false ? 'linear-gradient(180deg, rgba(18,27,47,.9), rgba(18,27,47,.62))' : 'rgba(18,27,47,.7)' }};
                border: 1px solid {{ $plan['recommended'] ?? false ? 'rgba(255,215,0,.4)' : 'var(--line)' }};
                border-radius: var(--radius-lg); padding: 25px; position: relative;">

                            @if($plan['recommended'] ?? false)
                                <div style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%);">
            <span style="background: var(--gold); color: var(--bg); padding: 5px 15px; border-radius: 20px;
                    font-size: 12px; font-weight: 700; white-space: nowrap;">
                <i class="bi bi-star-fill me-1"></i> RECOMMANDÉ
            </span>
                                </div>
                            @endif

                            <div style="text-align: center; margin-bottom: 20px;">
                                <h3 style="color: {{ $plan['recommended'] ?? false ? 'var(--gold)' : 'var(--text)' }};
                    margin-bottom: 10px; text-transform: uppercase;">
                                    {{ $plan['name'] }}
                                </h3>
                                <div style="font-size: 32px; font-weight: 900; color: var(--gold); margin-bottom: 5px;">
                                    ${{ $plan['price'] }}
                                </div>
                                <div style="color: var(--muted2); font-size: 14px;">par mois</div>
                            </div>

                            <ul style="list-style: none; padding: 0; margin: 0 0 25px 0;">
                                @foreach($plan['features'] as $feature)
                                    <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: var(--muted);">
                                        <i class="bi bi-check-circle-fill" style="color: var(--success);"></i>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <form action="{{ route('client.payment.process') }}" method="POST" style="margin-top: auto;">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $plan['name'] }}">
                                <button type="submit"
                                        class="btn {{ $plan['recommended'] ?? false ? 'btn-primary' : 'btn-secondary' }}"
                                        style="width: 100%;">
                                    <i class="bi bi-credit-card me-2"></i>
                                    Souscrire
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <!-- Historique des paiements -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 18px;">
                            <i class="bi bi-clock-history me-2"></i>
                            Historique des Paiements
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(count($payments) > 0)
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Plan</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y') }}</td>
                                            <td>
                                        <span class="badge" style="background: rgba(255,215,0,.1); color: var(--gold);
                                                padding: 4px 8px; border-radius: 12px; text-transform: uppercase;">
                                            {{ $payment['plan'] }}
                                        </span>
                                            </td>
                                            <td style="font-weight: 600;">${{ number_format($payment['amount'], 2) }}</td>
                                            <td>{{ $payment['method'] }}</td>
                                            <td>
                                                @if($payment['status'] === 'verified')
                                                    <span class="badge" style="background: rgba(46,204,113,.2); color: var(--success);
                                                    padding: 4px 8px; border-radius: 12px;">
                                                <i class="bi bi-check-circle me-1"></i> Vérifié
                                            </span>
                                                @elseif($payment['status'] === 'pending')
                                                    <span class="badge" style="background: rgba(255,215,0,.2); color: var(--gold);
                                                    padding: 4px 8px; border-radius: 12px;">
                                                <i class="bi bi-clock me-1"></i> En attente
                                            </span>
                                                @else
                                                    <span class="badge" style="background: rgba(231,76,60,.2); color: var(--error);
                                                    padding: 4px 8px; border-radius: 12px;">
                                                <i class="bi bi-x-circle me-1"></i> Échoué
                                            </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment['status'] === 'pending')
                                                    <button class="btn btn-secondary btn-sm">
                                                        <i class="bi bi-arrow-repeat"></i> Renouveler
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="bi bi-file-text"></i> Facture
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 40px; color: var(--muted);">
                                <i class="bi bi-credit-card" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p>Aucun paiement enregistré</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informations de paiement -->
                <div class="card" style="margin-top: 30px; background: rgba(26,35,126,.1); border-color: rgba(26,35,126,.3);">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 18px; color: #3498db;">
                            <i class="bi bi-info-circle me-2"></i>
                            Informations de Paiement
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 style="color: var(--text); margin-bottom: 15px;">Méthodes acceptées</h4>
                                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    <div style="padding: 10px 15px; background: rgba(255,255,255,.05);
                                        border-radius: 8px; border: 1px solid var(--line);">
                                        <i class="bi bi-credit-card-2-front" style="font-size: 24px; color: #3498db;"></i>
                                        <div style="font-size: 12px; color: var(--muted); margin-top: 5px;">Carte Bancaire</div>
                                    </div>
                                    <div style="padding: 10px 15px; background: rgba(255,255,255,.05);
                                        border-radius: 8px; border: 1px solid var(--line);">
                                        <i class="bi bi-paypal" style="font-size: 24px; color: #003087;"></i>
                                        <div style="font-size: 12px; color: var(--muted); margin-top: 5px;">PayPal</div>
                                    </div>
                                    <div style="padding: 10px 15px; background: rgba(255,255,255,.05);
                                        border-radius: 8px; border: 1px solid var(--line);">
                                        <i class="bi bi-bank" style="font-size: 24px; color: var(--success);"></i>
                                        <div style="font-size: 12px; color: var(--muted); margin-top: 5px;">Virement</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 style="color: var(--text); margin-bottom: 15px;">Support Paiement</h4>
                                <div style="color: var(--muted); line-height: 1.6;">
                                    <p><i class="bi bi-whatsapp me-2" style="color: #25D366;"></i> WhatsApp: +221 77 XXX XX XX</p>
                                    <p><i class="bi bi-envelope me-2"></i> Email: payment@14trades.com</p>
                                    <p><i class="bi bi-clock me-2"></i> Réponse sous 24h maximum</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des cartes de plan
            const planCards = document.querySelectorAll('.plan-card');
            planCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'transform 0.3s ease';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Gestion du formulaire de paiement
            const paymentForms = document.querySelectorAll('form[action*="payment.process"]');
            paymentForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const plan = this.querySelector('input[name="plan"]').value;

                    // Ici, tu intégreras Stripe, PayPal ou autre processeur
                    alert(`Redirection vers le processeur de paiement pour le plan ${plan.toUpperCase()}...`);

                    // Pour l'instant, on simule un succès
                    setTimeout(() => {
                        window.location.href = "{{ route('client.payment') }}?success=1";
                    }, 1000);
                });
            });
        });
    </script>
@endpush
