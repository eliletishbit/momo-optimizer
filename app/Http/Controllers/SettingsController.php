<?php

namespace App\Http\Controllers;

use App\Models\Method;
use App\Models\UserMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Affiche les paramètres utilisateur (moyens de paiement préférés).
     */
        public function index(Request $request): View|RedirectResponse
        {
            $user = Auth::user();

            if (! $user instanceof \App\Models\User) {
                return redirect()->route('login');
            }

            $userMethods = $user->userMethods()->with('method')->get();

            $selectedCategory = $request->query('category');

            // Récupérer toutes les méthodes actives (avec whereRaw pour éviter l'erreur booléen)
            $availableMethods = Method::whereRaw('is_active = true')
                ->when($selectedCategory, function ($query, $category) {
                    return $query->where('category', $category);
                })
                ->orderBy('name')
                ->get();

            // Récupérer les catégories distinctes
            $categories = Method::whereRaw('is_active = true')
                ->distinct()
                ->pluck('category')
                ->toArray();

            return view('pages.settings', [
                'userMethods' => $userMethods,
                'availableMethods' => $availableMethods,
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
            ]);
        }

    /**
     * Ajoute un moyen de paiement préféré.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof \App\Models\User) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'method_id' => 'required|exists:methods,id',
            'is_preferred_sending' => 'boolean',
            'is_preferred_receipt' => 'boolean',
        ]);

        $type = $this->determineType($request);

        // Vérifier si l'utilisateur a déjà cette méthode
        $existing = $user->userMethods()->where('method_id', $validated['method_id'])->first();

        if ($existing) {
            // Mise à jour du type et is_default
            $existing->update([
                'type' => $type,
                'is_default' => DB::raw('true'),
            ]);
        } else {
            // Création d'un nouvel enregistrement
            $user->userMethods()->create([
                'method_id' => $validated['method_id'],
                'type' => $type,
                'is_default' => DB::raw('false'),
            ]);
        }

        return back()->with('success', 'Moyen de paiement ajouté avec succès.');
    }

    /**
     * Modifie un moyen de paiement préféré.
     */
    public function update(Request $request, UserMethod $userMethod): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($userMethod->user_id !== Auth::id()) {
            abort(403, 'Accès interdit.');
        }

        $validated = $request->validate([
            'is_preferred_sending' => 'boolean',
            'is_preferred_receipt' => 'boolean',
        ]);

        $type = $this->determineType($request);
        $userMethod->update([
            'type' => $type,
            'is_default' => $request->boolean('is_default', false),
        ]);

        return back()->with('success', 'Paramètres mis à jour.');
    }

    /**
     * Supprime un moyen de paiement préféré.
     */
    public function destroy(UserMethod $userMethod): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($userMethod->user_id !== Auth::id()) {
            abort(403, 'Accès interdit.');
        }

        $userMethod->delete();

        return back()->with('success', 'Moyen de paiement supprimé.');
    }

    /**
     * Détermine le type (send, receipt, both) en fonction des préférences.
     */
    private function determineType(Request $request): string
    {
        $sending = $request->boolean('is_preferred_sending');
        $receipt = $request->boolean('is_preferred_receipt');

        if ($sending && $receipt) {
            return 'both';
        } elseif ($sending) {
            return 'send';
        } elseif ($receipt) {
            return 'receipt';
        } else {
            return 'both'; // Valeur par défaut
        }
    }
}