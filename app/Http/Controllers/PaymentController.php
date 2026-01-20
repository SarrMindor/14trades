<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Afficher l'historique des paiements de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculer plusieurs statistiques
        $stats = [
            'verified' => Payment::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount'),

            'pending' => Payment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount'),

            'total' => Payment::where('user_id', $user->id)
                ->sum('amount'),

            'count_verified' => Payment::where('user_id', $user->id)
                ->where('status', 'approved')
                ->count(),

            'count_pending' => Payment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        return view('admin.payments.history', compact('payments', 'user', 'stats'));
    }

    /**
     * Afficher la page de paiement
     */
    public function showCheckout()
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est approuvé
        if (!$user->is_approved) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte n\'est pas encore approuvé. Contactez l\'administrateur.');
        }

        // Déplacer les plans ici (pas en propriété protected)
        $plans = [
            'basic' => [
                'price' => 49,
                'accounts' => 1,
                'features' => [
                    'Bot Gold standard',
                    '1 compte MT5',
                    'Support email',
                    'Mises à jour mensuelles'
                ]
            ],
            'normal' => [
                'price' => 99,
                'accounts' => 3,
                'features' => [
                    'Tout du Basic',
                    'Jusqu\'à 3 comptes MT5',
                    'Support prioritaire',
                    'Mises à jour hebdomadaires',
                    'Accès anticipé aux nouvelles fonctionnalités'
                ]
            ],
            'elite' => [
                'price' => 199,
                'accounts' => 'Illimité',
                'features' => [
                    'Tout du Normal',
                    'Comptes MT5 illimités',
                    'Support 24/7 WhatsApp',
                    'Mises à jour en temps réel',
                    'Fonctionnalités avancées',
                    'Consultation personnalisée'
                ]
            ],
        ];

        return view('admin.payments.checkout', compact('plans', 'user'));
    }

    /**
     * Traiter le paiement
     */
    public function processPayment(Request $request)
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est approuvé
        if (!$user->is_approved) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte n\'est pas encore approuvé.');
        }

        $request->validate([
            'plan' => 'required|in:basic,normal,elite',
            'method' => 'required|in:wave,orange,mobile_money',
            'transaction_id' => 'nullable|string|max:50',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
        ], [
            'proof.required' => 'Veuillez télécharger une preuve de paiement.',
            'proof.mimes' => 'Le fichier doit être au format JPG, PNG ou PDF.',
            'proof.max' => 'Le fichier ne doit pas dépasser 5MB.'
        ]);

        // Déterminer le montant selon le plan
        $amount = $this->getAmountFromPlan($request->plan);

        // Sauvegarder la preuve de paiement
        $proofPath = $request->file('proof')->store('payments/' . date('Y/m'), 'public');

        // Créer l'enregistrement de paiement
        Payment::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'proof_path' => $proofPath,
            'status' => 'pending'
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', 'Paiement soumis avec succès ! L\'administrateur le vérifiera sous 24h.');
    }

    /**
     * Télécharger la preuve de paiement
     */
    public function downloadProof($id)
    {
        $payment = Payment::findOrFail($id);

        // Vérifier que l'utilisateur a le droit de voir ce fichier
        if (Auth::id() !== $payment->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier si le fichier existe
        if (!Storage::disk('public')->exists($payment->proof_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        $filename = 'preuve-paiement-' . $payment->id . '-' . $payment->user->name . '.' . pathinfo($payment->proof_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($payment->proof_path, $filename);
    }

    /**
     * Helper: déterminer le montant selon le plan
     */

    public function checkout()
    {
        $user = auth()->user();

        return view('client.checkout', compact('user'));
    }

    private function getAmountFromPlan($plan)
    {
        return match($plan) {
            'basic' => 49,
            'normal' => 99,
            'elite' => 199,
            default => 0
        };
    }

    /**
     * Helper: déterminer le plan selon le montant
     */
    private function getPlanFromAmount($amount)
    {
        return match($amount) {
            49 => 'basic',
            99 => 'normal',
            199 => 'elite',
            default => 'basic'
        };
    }
}
