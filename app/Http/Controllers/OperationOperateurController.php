<?php

namespace App\Http\Controllers;

use App\Models\OperationOperateur;
use App\Models\OperatorProfile;
use App\Models\TypeOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OperationOperateurController extends Controller
{
    /**
     * ✅ Liste des opérations avec filtres et pagination
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = OperationOperateur::where('user_id', $user->id)
            ->with('typeoperateur');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('reseau')) {
            $query->where('reseau', $request->reseau);
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('type_operation_id')) {
            $query->where('type_operation_id', $request->type_operation_id);
        }

        $operations = $query->orderBy('created_at', 'desc')->paginate(20);

        $types = TypeOperation::all();
        $reseaux = ['MTN', 'Moov', 'Celtiis', 'Orange'];
        $directions = ['entrant', 'sortant'];

        return view('operations.index', compact('operations', 'types', 'reseaux', 'directions'));
    }

    /**
     * ✅ Formulaire de création d'une opération
     */
    public function create()
    {
        $types = TypeOperation::all();
        $reseaux = ['MTN', 'Moov', 'Celtiis', 'Orange'];
        $directions = ['entrant', 'sortant'];

        $profile = OperatorProfile::where('user_id', Auth::id())->first();

        return view('operations.create', compact('types', 'reseaux', 'directions', 'profile'));
    }

    /**
     * ✅ Enregistrer une nouvelle opération avec vérification de liquidité
     */
    public function store(Request $request)
    {
        // ✅ Nettoyer le montant des séparateurs
        $request->merge([
            'montant' => str_replace([' ', ','], ['', '.'], $request->montant)
        ]);

        $validated = $request->validate([
            'type_operation_id' => 'required|exists:types_operation,id',
            'reseau' => 'required|string|max:50',
            'telephone_client' => 'nullable|string|max:20',
            'montant' => 'required|numeric|min:1',
            'direction' => 'required|in:entrant,sortant',
        ]);

        $user = Auth::user();
        $montant = (float) $validated['montant'];
        $direction = $validated['direction'];
        $reseau = $validated['reseau'];

        // ✅ Vérification de liquidité (bloque si insuffisant)
        $error = $this->checkLiquidite($user, $direction, $montant, $reseau);
        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        // ✅ PROTECTION ANTI-DOUBLON
        $existing = OperationOperateur::where('user_id', $user->id)
            ->where('montant', $montant)
            ->where('direction', $direction)
            ->where('reseau', $reseau)
            ->where('type_operation_id', $validated['type_operation_id'])
            ->where('telephone_client', $validated['telephone_client'] ?? null)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($existing) {
            return redirect()->route('operations.index')
                ->with('error', '⚠️ Cette opération vient d\'être enregistrée. Évitez le double clic.');
        }

        // ✅ Créer l'opération
        OperationOperateur::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'type_operation_id' => $validated['type_operation_id'],
            'reseau' => $reseau,
            'telephone_client' => $validated['telephone_client'] ?? null,
            'montant' => $montant,
            'direction' => $direction,
        ]);

        // ✅ Mettre à jour les caisses
        $this->updateCaisses($user, $direction, $montant, $reseau);

        return redirect()->route('operations.index')
            ->with('success', '✅ Opération enregistrée avec succès !');
    }

    /**
     * ✅ Vérification de liquidité (caisse physique pour retrait, sous-compte virtuel pour dépôt)
     * Retourne un message d'erreur ou null si tout est OK
     */
    private function checkLiquidite($user, string $direction, float $montant, string $reseau): ?string
    {
        $profile = OperatorProfile::where('user_id', $user->id)->first();
        if (!$profile) {
            return 'Profil opérateur introuvable.';
        }

        if ($direction === 'entrant') {
            // Dépôt : le sous-compte virtuel du réseau doit avoir assez de solde
            $subaccount = $profile->virtualSubaccounts()->where('reseau', $reseau)->first();
            if (!$subaccount || $subaccount->solde < $montant) {
                $soldeActuel = $subaccount ? $subaccount->solde : 0;
                return '❌ Solde insuffisant sur le sous-compte virtuel ' . $reseau . ' ('
                    . number_format($soldeActuel, 0, ',', ' ')
                    . ' FCFA) pour ce dépôt de '
                    . number_format($montant, 0, ',', ' ') . ' FCFA.';
            }
        } else {
            // Retrait : la caisse physique doit avoir assez de liquidité
            if ($profile->caisse_physique < $montant) {
                return '❌ Solde insuffisant en caisse physique ('
                    . number_format($profile->caisse_physique, 0, ',', ' ')
                    . ' FCFA) pour ce retrait de '
                    . number_format($montant, 0, ',', ' ') . ' FCFA.';
            }
        }

        return null;
    }

    /**
     * ✅ Mise à jour des deux caisses et du sous-compte virtuel
     * 
     * 🔄 ENTRANT (Dépôt) :
     *    - caisse_physique AUGMENTE (+ montant)
     *    - sous-compte virtuel du réseau DIMINUE (- montant)
     * 
     * 🔄 SORTANT (Retrait) :
     *    - caisse_physique DIMINUE (- montant)
     *    - sous-compte virtuel du réseau AUGMENTE (+ montant)
     */
    private function updateCaisses($user, string $direction, float $montant, string $reseau): void
    {
        $profile = OperatorProfile::where('user_id', $user->id)->first();
        if (!$profile) return;

        // 1. Mettre à jour la caisse physique
        if ($direction === 'entrant') {
            $profile->caisse_physique += $montant;
        } else {
            $profile->caisse_physique -= $montant;
        }

        // 2. Mettre à jour le sous-compte virtuel du réseau concerné
        $subaccount = $profile->virtualSubaccounts()->where('reseau', $reseau)->first();
        if (!$subaccount) {
            // Créer le sous-compte s'il n'existe pas (normalement créé à l'inscription)
            $subaccount = $profile->virtualSubaccounts()->create([
                'reseau' => $reseau,
                'solde' => 0,
            ]);
        }

        if ($direction === 'entrant') {
            // Dépôt : le sous-compte virtuel diminue
            $subaccount->solde -= $montant;
        } else {
            // Retrait : le sous-compte virtuel augmente
            $subaccount->solde += $montant;
        }
        $subaccount->save();

        // 3. Recalculer la caisse virtuelle globale (somme des sous-comptes)
        $profile->recalculateVirtualCaisse();

        // 4. Sauvegarder la caisse physique (déjà modifiée)
        $profile->save();
    }

    /**
     * ✅ Supprimer une opération (annulation)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $operation = OperationOperateur::where('user_id', $user->id)->findOrFail($id);

        // ✅ Rétablir les caisses en annulant l'opération
        $profile = OperatorProfile::where('user_id', $user->id)->first();
        if ($profile) {
            // Récupérer le sous-compte virtuel du réseau
            $subaccount = $profile->virtualSubaccounts()->where('reseau', $operation->reseau)->first();

            if ($operation->direction === 'entrant') {
                // Annulation d'un dépôt : physique -, sous-compte +
                $profile->caisse_physique -= $operation->montant;
                if ($subaccount) {
                    $subaccount->solde += $operation->montant;
                    $subaccount->save();
                }
            } else {
                // Annulation d'un retrait : physique +, sous-compte -
                $profile->caisse_physique += $operation->montant;
                if ($subaccount) {
                    $subaccount->solde -= $operation->montant;
                    $subaccount->save();
                }
            }
            // Recalculer la caisse virtuelle globale
            $profile->recalculateVirtualCaisse();
            $profile->save();
        }

        $operation->delete();

        return redirect()->route('operations.index')
            ->with('success', '🗑️ Opération supprimée avec succès.');
    }

    /**
     * ✅ Bilan journalier avec groupement par réseau et type
     */
    public function bilan(Request $request)
    {
        $user = Auth::user();
        $date = $request->date ?? Carbon::today()->format('Y-m-d');

        $operations = OperationOperateur::where('user_id', $user->id)
            ->whereDate('created_at', $date)
            ->with('typeoperateur')
            ->get();

        $profile = OperatorProfile::where('user_id', $user->id)->first();

        $report = [];
        $grandTotals = [
            'count' => 0,
            'total_entrant' => 0,
            'total_sortant' => 0,
            'net' => 0,
            'montant_total' => 0,
        ];

        $reseaux = ['MTN', 'Moov', 'Celtiis', 'Orange'];
        $types = TypeOperation::all();

        foreach ($reseaux as $reseau) {
            foreach ($types as $type) {
                $key = $reseau . '_' . $type->id;
                $report[$key] = [
                    'reseau' => $reseau,
                    'type' => $type,
                    'count' => 0,
                    'total_entrant' => 0,
                    'total_sortant' => 0,
                    'net' => 0,
                    'montant_total' => 0,
                ];
            }
        }

        foreach ($operations as $op) {
            $key = $op->reseau . '_' . $op->type_operation_id;
            if (!isset($report[$key])) {
                $report[$key] = [
                    'reseau' => $op->reseau,
                    'type' => $op->typeoperateur,
                    'count' => 0,
                    'total_entrant' => 0,
                    'total_sortant' => 0,
                    'net' => 0,
                    'montant_total' => 0,
                ];
            }

            $report[$key]['count']++;
            $report[$key]['montant_total'] += $op->montant;

            if ($op->direction === 'entrant') {
                $report[$key]['total_entrant'] += $op->montant;
            } else {
                $report[$key]['total_sortant'] += $op->montant;
            }

            $grandTotals['count']++;
            $grandTotals['montant_total'] += $op->montant;
            if ($op->direction === 'entrant') {
                $grandTotals['total_entrant'] += $op->montant;
            } else {
                $grandTotals['total_sortant'] += $op->montant;
            }
        }

        foreach ($report as $key => &$data) {
            $data['net'] = $data['total_entrant'] - $data['total_sortant'];
        }
        $grandTotals['net'] = $grandTotals['total_entrant'] - $grandTotals['total_sortant'];

        $report = array_filter($report, function ($item) {
            return $item['count'] > 0;
        });

        usort($report, function ($a, $b) {
            if ($a['reseau'] === $b['reseau']) {
                return $a['type']->nom <=> $b['type']->nom;
            }
            return $a['reseau'] <=> $b['reseau'];
        });

        return view('operations.bilan', [
            'report' => $report,
            'grandTotals' => $grandTotals,
            'date' => $date,
            'profile' => $profile,
            'totalOperations' => $operations->count(),
        ]);
    }

    /**
     * ✅ Afficher le formulaire de mise à jour des caisses
     */
    public function editCaisses()
    {
        $user = Auth::user();
        $profile = OperatorProfile::with('virtualSubaccounts')
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return redirect()->route('operations.index')
                ->with('error', 'Profil opérateur introuvable.');
        }

        return view('operations.caisses', compact('profile'));
    }

    /**
     * ✅ Mettre à jour les caisses manuellement (via formulaire)
     * Met à jour la caisse physique et les sous-comptes virtuels, puis recalcule la caisse virtuelle globale.
     */
    public function updateCaissesManually(Request $request)
    {
        $user = Auth::user();
        $profile = OperatorProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return redirect()->back()->with('error', 'Profil opérateur introuvable.');
        }

        $validated = $request->validate([
            'caisse_physique' => 'required|numeric|min:0',
            'subaccounts' => 'nullable|array',
            'subaccounts.*' => 'nullable|numeric|min:0',
        ]);

        // Mettre à jour la caisse physique
        $profile->caisse_physique = $validated['caisse_physique'];

        // Mettre à jour les sous-comptes virtuels
        if ($request->has('subaccounts')) {
            foreach ($validated['subaccounts'] as $id => $solde) {
                $sub = $profile->virtualSubaccounts()->find($id);
                if ($sub) {
                    $sub->solde = (float) $solde;
                    $sub->save();
                }
            }
        }

        // Recalculer la caisse virtuelle globale
        $profile->recalculateVirtualCaisse();
        $profile->save();

        return redirect()->route('operations.bilan')
            ->with('success', '✅ Caisses et sous-comptes mis à jour avec succès.');
    }
}