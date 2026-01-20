<?php
// app/Http/Middleware/ClientMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'client') {
            abort(403, 'Accès réservé aux clients.');
        }

        // Vérifier si le client est approuvé
        if (!auth()->user()->is_approved) {
            return redirect()->route('waiting.approval');
        }

        return $next($request);
    }
}
