<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MethodController extends Controller
{
    /**
     * Liste des moyens de paiement.
     */
    public function index(): View
    {
        $methods = Method::paginate(20);
        return view('pages.admin.methods.index', compact('methods'));
    }

    /**
     * Formulaire de création.
     */
    public function create(): View
    {
        return view('pages.admin.methods.create');
    }

    /**
     * Enregistre un nouveau moyen.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:methods,code',
            'category' => 'required|string|in:mobile_money,bank,international,crypto,other',
            'country_code' => 'required|string|max:10',
            'logo_url' => 'nullable|url|max:255',
            'color_primary' => 'nullable|string|max:7',
            'currency' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
        ]);

        $countryCode = strtoupper(trim($validated['country_code']));

        Country::firstOrCreate(
            ['code' => $countryCode],
            ['name' => $this->countryNameFromCode($countryCode), 'currency' => $validated['currency'] ?? 'XOF', 'is_active' => true]
        );

        Method::create([
            ...$validated,
            'country_code' => $countryCode,
            'currency' => $validated['currency'] ?? 'XOF',
        ]);

        return redirect()->route('admin.methods.index')->with('success', 'Moyen de paiement créé.');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Method $method): View
    {
        return view('pages.admin.methods.edit', compact('method'));
    }

    /**
     * Met à jour un moyen.
     */
    public function update(Request $request, Method $method): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:methods,code,' . $method->id,
            'category' => 'required|string|in:mobile_money,bank,international,crypto,other',
            'country_code' => 'required|string|max:10',
            'logo_url' => 'nullable|url|max:255',
            'color_primary' => 'nullable|string|max:7',
            'currency' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
        ]);

        $countryCode = strtoupper(trim($validated['country_code']));

        Country::firstOrCreate(
            ['code' => $countryCode],
            ['name' => $this->countryNameFromCode($countryCode), 'currency' => $validated['currency'] ?? 'XOF', 'is_active' => true]
        );

        $method->update([
            ...$validated,
            'country_code' => $countryCode,
            'currency' => $validated['currency'] ?? 'XOF',
        ]);

        return redirect()->route('admin.methods.index')->with('success', 'Moyen de paiement mis à jour.');
    }

    private function countryNameFromCode(string $code): string
    {
        $names = [
            'BJ' => 'Bénin',
            'CI' => 'Côte d\'Ivoire',
            'TG' => 'Togo',
            'BF' => 'Burkina Faso',
            'SN' => 'Sénégal',
            'CM' => 'Cameroun',
            'ML' => 'Mali',
            'GN' => 'Guinée',
        ];

        return $names[$code] ?? ucfirst(strtolower($code));
    }

    /**
     * Supprime un moyen.
     */
    public function destroy(Method $method): RedirectResponse
    {
        $method->delete();
        return redirect()->route('admin.methods.index')->with('success', 'Moyen de paiement supprimé.');
    }
}
