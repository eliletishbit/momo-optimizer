<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'utilisateur.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Vous devez être connecté pour accéder à cette page.');
        }

        // ✅ Récupérer les optimisations récentes
        $recentOptimizations = $user->optimizationHistory()
            ->latest()
            ->take(5)
            ->get();

        // ✅ Compter le nombre total d'optimisations
        $totalOptimizations = $user->optimizationHistory()->count();

        // ✅ Calculer le total des économies
        $totalSavings = $user->optimizationHistory()->sum('savings');

        // ✅ Récupérer le nombre de moyens de paiement
        $methodCount = $user->userMethods()->count();

        return view('pages.dashboard', [
            'user' => $user,
            'recentOptimizations' => $recentOptimizations,
            'totalOptimizations' => $totalOptimizations,
            'totalSavings' => $totalSavings,
            'methodCount' => $methodCount,
            'subscription_status' => $user->getSubscriptionStatus(),
            'subscription_message' => $user->getSubscriptionMessage(),
            'remaining_credits' => $user->getPayAsYouGoRemainingUses(),
            'trial_days_left' => $user->getFreeTrialDaysLeft(),
        ]);
    }
}