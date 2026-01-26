<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MT5AccountController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Admin\ClientController;
use App\Models\User;
use App\Models\Payment;

/*
|--------------------------------------------------------------------------
| Page d'accueil
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Routes protégées (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard général
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | CLIENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('client')->name('client.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::view('/accounts', 'client.accounts')->name('accounts');
        Route::view('/trades', 'client.trades')->name('trades');
        Route::view('/performance', 'client.performance')->name('performance');
        Route::view('/analytics', 'client.analytics')->name('analytics');

        Route::get('/payment', [PaymentController::class, 'index'])->name('payment');

        /*
        | MT5 Accounts
        */
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [MT5AccountController::class, 'index'])->name('index');
            Route::get('/create', [MT5AccountController::class, 'create'])->name('create');
            Route::post('/', [MT5AccountController::class, 'store'])->name('store');
            Route::delete('/{id}', [MT5AccountController::class, 'destroy'])->name('destroy');

            Route::post('/sync', [MT5AccountController::class, 'sync'])->name('sync');
            Route::post('/sync/{id}', [MT5AccountController::class, 'sync'])->name('sync.one');
            Route::post('/sync-all', [MT5AccountController::class, 'syncAll'])->name('syncAll');
        });

        /*
        | Paiements client
        */
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout');
            Route::post('/process', [PaymentController::class, 'processPayment'])->name('process');
            Route::get('/proof/{id}', [PaymentController::class, 'downloadProof'])->name('proof');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('can:isAdmin')->group(function () {

        Route::get('/dashboard', function () {

            $stats = [
                'total_clients'     => User::where('role', 'client')->count(),
                'pending_approvals' => User::where('is_approved', false)->count(),
                'pending_payments'  => Payment::where('status', 'pending')->count(),
                'total_earnings'    => Payment::where('status', 'approved')->sum('amount'),
            ];

            return view('admin.dashboard', compact('stats'));
        })->name('dashboard');

        /*
        | Clients
        */
        Route::get('/clients', [ClientController::class, 'index'])->name('clients');
        Route::post('/clients/{user}/approve', [ClientController::class, 'approve'])->name('clients.approve');

        Route::post('/clients/{id}/activate', function ($id) {
            $client = User::findOrFail($id);
            $client->update([
                'subscription_status' => 'active',
                'plan' => request('plan', 'normal'),
                'subscription_ends_at' => now()->addMonths(request('months', 1)),
            ]);

            return back()->with('success', 'Abonnement activé');
        })->name('clients.activate');

        /*
        | Paiements
        */
        Route::get('/payments', function () {
            $payments = Payment::with('user')->latest()->paginate(20);
            return view('admin.payments.index', compact('payments'));
        })->name('payments');

        Route::get('/payments/{id}', function ($id) {
            $payment = Payment::with('user')->findOrFail($id);
            return view('admin.payments.show', compact('payment'));
        })->name('payments.show');

        Route::post('/payments/{id}/verify', function ($id) {
            $payment = Payment::findOrFail($id);
            $payment->update([
                'status' => 'approved',
                'verified_at' => now()
            ]);

            $payment->user->update([
                'is_approved' => true,
                'plan' => match ($payment->amount) {
                    49 => 'basic',
                    99 => 'normal',
                    199 => 'elite',
                    default => 'basic',
                }
            ]);

            return back()->with('success', 'Paiement validé');
        })->name('payments.verify');

        Route::post('/payments/{id}/reject', function ($id) {
            Payment::findOrFail($id)->update([
                'status' => 'rejected',
                'admin_notes' => request('reason')
            ]);

            return back()->with('success', 'Paiement rejeté');
        })->name('payments.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
