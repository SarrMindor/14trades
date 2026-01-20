<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
require __DIR__.'/auth.php';

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    // Dashboard principal (redirige selon rôle)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes client
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/accounts', function () { return view('client.accounts'); })->name('accounts');
        Route::get('/trades', function () { return view('client.trades'); })->name('trades');
        Route::get('/performance', function () { return view('client.performance'); })->name('performance');
        Route::get('/analytics', function () { return view('client.analytics'); })->name('analytics');
        Route::get('/payment', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payment');
        // Routes complètes pour les comptes
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MT5AccountController::class, 'index'])->name('index');
        Route::post('/add', [\App\Http\Controllers\MT5AccountController::class, 'store'])->name('add');
        Route::delete('/{id}', [\App\Http\Controllers\MT5AccountController::class, 'destroy'])->name('destroy');
        Route::post('/sync', [\App\Http\Controllers\MT5AccountController::class, 'sync'])->name('sync');
        Route::get('/{id}', [\App\Http\Controllers\MT5AccountController::class, 'show'])->name('show');
    });
        // Routes de paiement pour client
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout');
            Route::post('/process', [PaymentController::class, 'processPayment'])->name('process');
            Route::get('/proof/{id}', [PaymentController::class, 'downloadProof'])->name('proof.download');
        });
    });

    // Routes admin
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard admin
        Route::get('/dashboard', function () {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $stats = [
                'total_clients' => \App\Models\User::where('role', 'client')->count(),
                'pending_approvals' => \App\Models\User::where('is_approved', false)->count(),
                'pending_payments' => \App\Models\Payment::where('status', 'pending')->count(),
                'total_earnings' => \App\Models\Payment::where('status', 'approved')->sum('amount'),
            ];

            $recentClients = \App\Models\User::where('role', 'client')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $recentPayments = \App\Models\Payment::with('user')->latest()->take(5)->get();
            $pendingAccounts = \App\Models\User::where('is_approved', false)->get();

            return view('admin.dashboard', compact('stats', 'recentClients', 'recentPayments', 'pendingAccounts'));
        })->name('dashboard');

        // Gestion des clients
        Route::get('/clients', function () {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $clients = \App\Models\User::where('role', 'client')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $activeClients = \App\Models\User::where('role', 'client')
                ->where('is_approved', true)
                ->where('subscription_status', 'active')
                ->count();

            $pendingClients = \App\Models\User::where('role', 'client')
                ->where('is_approved', false)
                ->count();

            return view('admin.clients.index', compact('clients', 'activeClients', 'pendingClients'));
        })->name('clients');

        // Approuver un client
        Route::post('/clients/{id}/approve', function ($id) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $client = \App\Models\User::findOrFail($id);
            $client->is_approved = true;
            $client->save();

            return redirect()->route('admin.clients')
                ->with('success', 'Client approuvé avec succès');
        })->name('clients.approve');

        // Activer un abonnement
        Route::post('/clients/{id}/activate', function ($id) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $client = \App\Models\User::findOrFail($id);
            $client->subscription_status = 'active';
            $client->plan = request('plan', 'normal');
            $client->subscription_ends_at = now()->addMonths(request('months', 1));
            $client->save();

            return redirect()->route('admin.clients')
                ->with('success', 'Abonnement activé avec succès');
        })->name('clients.activate');

        // Gestion des paiements (Admin)
        Route::get('/payments', function () {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $payments = \App\Models\Payment::with('user')->latest()->paginate(20);

            $verifiedAmount = \App\Models\Payment::where('status', 'approved')->sum('amount');
            $pendingAmount = \App\Models\Payment::where('status', 'pending')->sum('amount');
            $totalRevenue = \App\Models\Payment::sum('amount');
            $totalTransactions = \App\Models\Payment::count();
            $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
            $pendingCount = \App\Models\Payment::where('status', 'pending')->count();

            return view('admin.payments.index', compact(
                'payments', 'verifiedAmount', 'pendingAmount',
                'totalRevenue', 'totalTransactions', 'averageTransaction', 'pendingCount'
            ));
        })->name('payments');

        // Routes supplémentaires pour admin (détails, vérification, etc.)
        Route::prefix('payments')->name('payments.')->group(function () {
            // Voir les détails d'un paiement
            Route::get('/{id}', function ($id) {
                if (auth()->user()->role !== 'admin') {
                    abort(403, 'Accès non autorisé');
                }

                $payment = \App\Models\Payment::with('user')->findOrFail($id);
                return view('admin.payments.show', compact('payment'));
            })->name('show');

            // Vérifier un paiement
            Route::post('/{id}/verify', function ($id) {
                if (auth()->user()->role !== 'admin') {
                    abort(403, 'Accès non autorisé');
                }

                $payment = \App\Models\Payment::findOrFail($id);
                $payment->status = 'approved';
                $payment->verified_at = now();
                $payment->save();

                // Activer l'utilisateur
                $user = $payment->user;
                $user->is_approved = true;
                $plan = match($payment->amount) {
                    49 => 'basic',
                    99 => 'normal',
                    199 => 'elite',
                    default => 'basic'
                };
                $user->plan = $plan;
                $user->save();

                return redirect()->route('admin.payments')
                    ->with('success', 'Paiement vérifié avec succès');
            })->name('verify');

            // Rejeter un paiement
            Route::post('/{id}/reject', function ($id) {
                if (auth()->user()->role !== 'admin') {
                    abort(403, 'Accès non autorisé');
                }

                $payment = \App\Models\Payment::findOrFail($id);
                $payment->status = 'rejected';
                $payment->admin_notes = request('reason');
                $payment->save();

                return redirect()->route('admin.payments')
                    ->with('success', 'Paiement rejeté avec succès');
            })->name('reject');
        });

        // Gestion des utilisateurs
        Route::get('/users', function () {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(20);

            $activeUsers = \App\Models\User::where('is_approved', true)
                ->where('subscription_status', 'active')
                ->count();

            $inactiveUsers = \App\Models\User::where('is_approved', false)
                ->orWhere('subscription_status', '!=', 'active')
                ->count();

            return view('admin.users.index', compact('users', 'activeUsers', 'inactiveUsers'));
        })->name('users');

        // Éditer un utilisateur
        Route::get('/users/{id}/edit', function ($id) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $user = \App\Models\User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        })->name('users.edit');

        // Mettre à jour un utilisateur
        Route::put('/users/{id}', function ($id) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Accès non autorisé');
            }

            $user = \App\Models\User::findOrFail($id);

            $validated = request()->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:admin,client,moderator',
                'is_approved' => 'boolean',
                'subscription_status' => 'in:pending,active,expired',
                'plan' => 'nullable|in:basic,normal,elite',
            ]);

            $user->update($validated);

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur mis à jour avec succès');
        })->name('users.update');
    }); // Fin du groupe admin

    // Routes profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
}); // Fin du middleware auth
