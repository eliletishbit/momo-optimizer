<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, ?string $requiredPlan = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // ✅ Si un plan spécifique est requis
        if ($requiredPlan) {
            $hasRequiredPlan = $this->userHasPlan($user, $requiredPlan);
            
            if (!$hasRequiredPlan) {
                $planName = ucfirst(str_replace('_', ' ', $requiredPlan));
                
                if ($user->hasActivePaidSubscription()) {
                    return redirect()->route('pricing')->with('error', 
                        "Cette fonctionnalité nécessite un abonnement {$planName}. Passez à un forfait supérieur."
                    );
                }
                
                return redirect()->route('pricing')->with('error', 
                    "Cette fonctionnalité nécessite un abonnement {$planName}."
                );
            }
            
            return $next($request);
        }

        // ✅ CORRECTION : Vérifier un abonnement PAYANT actif (pas l'essai gratuit)
        if ($user->hasActivePaidSubscription()) {
            return $next($request);
        }

        // ✅ Vérifier le mode dégradé
        if ($user->isDegraded()) {
            return redirect()->route('pricing')->with('error', 
                'Votre essai gratuit a expiré. Souscrivez à un forfait Premium, Pro ou Pay As You Go pour continuer.'
            );
        }

        if ($user->hasExpiredFreeTrial()) {
            return redirect()->route('pricing')->with('error', 
                'Votre essai gratuit a expiré. Souscrivez à un forfait premium, pro ou pay-as-you-go pour continuer.'
            );
        }

        return redirect()->route('pricing')->with('error', 
            'Cette fonctionnalité nécessite un abonnement Premium, Pro ou Pay As You Go.'
        );
    }

    private function userHasPlan($user, string $plan): bool
    {
        if ($user->subscription === $plan && $user->subscription_expires_at !== null) {
            if ($user->subscription_expires_at->isFuture()) {
                return true;
            }
            return false;
        }

        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->where('plan', $plan)
            ->first();

        return $activeSubscription !== null;
    }
}