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
                if ($requiredPlan === 'pro') {
                    return redirect()->route('pricing')->with('error', 
                        "Veuillez passer à l'abonnement Pro pour utiliser cette fonctionnalité."
                    );
                }

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
        // 👑 Les administrateurs ont accès à toutes les fonctionnalités
        if ($user->is_admin) {
            return true;
        }

        // Définir la hiérarchie des forfaits
        $hierarchy = [
            'free' => 0,
            'pay_as_you_go' => 1,
            'premium' => 2,
            'pro' => 3,
            'business' => 4,
        ];

        $requiredLevel = $hierarchy[$plan] ?? 0;

        // 1. Vérification sur le champ direct du modèle User
        if ($user->subscription && isset($hierarchy[$user->subscription])) {
            $userLevel = $hierarchy[$user->subscription];
            if ($userLevel >= $requiredLevel) {
                // Si une date d'expiration existe, elle doit être dans le futur
                if ($user->subscription_expires_at === null || $user->subscription_expires_at->isFuture()) {
                    return true;
                }
            }
        }

        // 2. Vérification dans la table des souscriptions actives
        $activePlans = $user->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->pluck('plan')
            ->toArray();

        foreach ($activePlans as $p) {
            if (($hierarchy[$p] ?? 0) >= $requiredLevel) {
                return true;
            }
        }

        return false;
    }
}