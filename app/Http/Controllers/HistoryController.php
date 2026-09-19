<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HistoryController extends Controller
{
    /**
     * Affiche l'historique des optimisations de l'utilisateur.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Vous devez être connecté pour accéder à cette page.');
        }

        // ✅ Récupérer l'historique avec pagination (TOUJOURS FRAIS)
        $history = $user->optimizationHistory()
            ->with('selectedMethod')
            ->latest()
            ->paginate(15);

        // ✅ Statistiques calculées DIRECTEMENT (pas de cache pour éviter les délais)
        $totalOptimizations = $user->optimizationHistory()->count();
        $totalSavings = $user->optimizationHistory()->sum('savings');
        $averageSavings = $totalOptimizations > 0 ? round($totalSavings / $totalOptimizations, 0) : 0;

        // ✅ Dernières optimisations
        $recentOptimizations = $user->optimizationHistory()
            ->with('selectedMethod')
            ->latest()
            ->limit(5)
            ->get();

        return view('pages.history', [
            'history' => $history,
            'totalOptimizations' => $totalOptimizations,
            'totalSavings' => $totalSavings,
            'averageSavings' => $averageSavings,
            'recentOptimizations' => $recentOptimizations,
        ]);
    }

    /**
     * Rafraîchir les statistiques (requête AJAX).
     */
    public function refreshStats(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 403);
        }

        // ✅ Calcul direct - pas de cache
        $totalOptimizations = $user->optimizationHistory()->count();
        $totalSavings = $user->optimizationHistory()->sum('savings');
        $averageSavings = $totalOptimizations > 0 ? round($totalSavings / $totalOptimizations, 0) : 0;

        $recentOptimizations = $user->optimizationHistory()
            ->with('selectedMethod')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'amount' => $item->amount,
                    'savings' => $item->savings,
                    'type' => $item->type,
                    'total_fee' => $item->total_fee,
                    'created_at' => $item->created_at->diffForHumans(),
                    'method_name' => $item->selectedMethod?->name ?? 'N/A',
                ];
            });

        return response()->json([
            'totalOptimizations' => $totalOptimizations,
            'totalSavings' => $totalSavings,
            'averageSavings' => $averageSavings,
            'recentOptimizations' => $recentOptimizations,
        ]);
    }

    /**
     * Supprimer une optimisation de son historique personnel.
     */
    public function destroy($id): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Vous devez être connecté.');
        }

        $optimization = $user->optimizationHistory()->findOrFail($id);
        $optimization->delete();

        return redirect()->route('history')->with('success', 'Optimisation supprimée de votre historique avec succès.');
    }

    /**
     * Réinitialiser complètement son historique d'optimisations.
     */
    public function clear(): \Illuminate\Http\RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Vous devez être connecté.');
        }

        $user->optimizationHistory()->delete();

        return redirect()->route('history')->with('success', 'Votre historique d\'optimisations a été réinitialisé avec succès.');
    }
}