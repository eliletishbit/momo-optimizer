<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production')) {
            // Si la requête est sécurisée ou si le reverse proxy (Render) indique du HTTPS, on continue
            if ($request->secure() || $request->header('x-forwarded-proto') === 'https') {
                return $next($request);
            }
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}