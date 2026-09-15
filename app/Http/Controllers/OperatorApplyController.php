<?php

namespace App\Http\Controllers;

use App\Models\OperatorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorApplyController extends Controller
{
    public function index()
    {
        return view('operator.apply');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isOperator()) {
            return redirect()->route('operations.index')
                ->with('success', 'Vous êtes déjà opérateur !');
        }

        if ($user->hasOperatorProfile()) {
            return redirect()->route('operator.apply')
                ->with('error', 'Vous avez déjà un profil opérateur.');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'caisse_physique' => 'nullable|numeric|min:0',
            'caisse_virtuelle' => 'nullable|numeric|min:0',
        ]);

        // ✅ Créer le profil
        $profile = OperatorProfile::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'is_active' => DB::raw('true'), // ✅ PostgreSQL boolean
            'approved_at' => now(),
            'approved_by' => $user->id,
            'caisse_physique' => $validated['caisse_physique'] ?? 0,
            'caisse_virtuelle' => $validated['caisse_virtuelle'] ?? 0,
            'caisse_date' => now()->toDateString(),
        ]);

        // ✅ Initialiser les sous-comptes virtuels pour chaque réseau
        $profile->initializeVirtualSubaccounts();

        return redirect()->route('operations.index')
            ->with('success', '✅ Félicitations ! Vous êtes maintenant opérateur.');
    }
}