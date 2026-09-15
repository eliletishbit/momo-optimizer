<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class IsOperator
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter');
        }

        // ✅ AJOUTER LES PARENTHÈSES !
        if (!$user->canAccessOperatorFeatures()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès aux fonctionnalités opérateur.');
        }

        return $next($request);
    }
}
