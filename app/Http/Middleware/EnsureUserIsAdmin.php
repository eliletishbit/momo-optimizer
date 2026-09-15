<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifie si l'utilisateur est connecté et s'il est admin
        if ($request->user() && $request->user()->is_admin) {
            return $next($request);
        }

        // Redirection vers l'accueil avec un message d'erreur si non autorisé
        return redirect('/')->with('error', 'Accès refusé. Vous devez être administrateur.');
    }
}
