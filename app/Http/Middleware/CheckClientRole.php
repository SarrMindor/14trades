<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClientRole
{
    /**
     * Vérifie que l'utilisateur a le rôle 'client'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Vérifie si l'utilisateur est un client (role = 'client')
        if ($user->role !== 'client') {
            // Si c'est un admin, on peut le rediriger vers le dashboard admin
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // Pour les autres rôles ou si le rôle est indéfini
            abort(403, 'Accès réservé aux clients.');
        }

        return $next($request);
    }
}
