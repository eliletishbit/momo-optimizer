<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReceiptFee;
use App\Models\SendingFee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeController extends Controller
{
    /**
     * Gestion des frais de réception.
     */
    public function receipt(): View
    {
        $fees = ReceiptFee::with('method')->paginate(20);
        return view('pages.admin.fees.receipt', compact('fees'));
    }

    /**
     * Gestion des frais d'envoi.
     */
    public function sending(): View
    {
        $fees = SendingFee::with('method')->paginate(20);
        return view('pages.admin.fees.sending', compact('fees'));
    }

    /**
     * Ajoute un palier de frais.
     */
    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $feeType = $request->input('fee_type', $request->input('type_name'));

        $validated = $request->validate([
            'method_id' => 'required|exists:methods,id',
            'country_code' => 'sometimes|string|max:10',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'fee_type' => 'sometimes|in:fixed,percentage',
            'type_name' => 'sometimes|in:fixed,percentage',
            'fee_amount' => 'nullable|numeric|min:0',
            'fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $method = \App\Models\Method::findOrFail($validated['method_id']);
        $resolvedCountryCode = strtoupper(trim((string) ($validated['country_code'] ?? $method->country_code ?? 'BJ')));
        $resolvedFeeType = $feeType
            ?? ($validated['fee_type'] ?? $validated['type_name'] ?? null);

        if ($resolvedFeeType === null) {
            $resolvedFeeType = isset($validated['fee_percentage']) && (float) $validated['fee_percentage'] > 0
                ? 'percentage'
                : 'fixed';
        }

        $validated['fee_type'] = $resolvedFeeType;
        $validated['country_code'] = $resolvedCountryCode;
        $validated['fee_amount'] = $resolvedFeeType === 'percentage'
            ? ($validated['fee_percentage'] ?? 0)
            : ($validated['fee_amount'] ?? 0);

        if ($type === 'receipt') {
            ReceiptFee::create($validated);
        } else {
            SendingFee::create($validated);
        }

        return back()->with('success', 'Palier de frais ajouté.');
    }

    /**
     * Modifie un palier de frais.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $type = $request->input('type');
        $feeType = $request->input('fee_type', $request->input('type_name'));

        $validated = $request->validate([
            'country_code' => 'sometimes|string|max:10',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'fee_type' => 'sometimes|in:fixed,percentage',
            'type_name' => 'sometimes|in:fixed,percentage',
            'fee_amount' => 'nullable|numeric|min:0',
            'fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $fee = ($type === 'receipt') ? ReceiptFee::findOrFail($id) : SendingFee::findOrFail($id);
        $resolvedCountryCode = strtoupper(trim((string) ($validated['country_code'] ?? $fee->country_code ?? $fee->method->country_code ?? 'BJ')));
        $resolvedFeeType = $feeType
            ?? ($validated['fee_type'] ?? $validated['type_name'] ?? null);

        if ($resolvedFeeType === null) {
            $resolvedFeeType = isset($validated['fee_percentage']) && (float) $validated['fee_percentage'] > 0
                ? 'percentage'
                : ($fee->fee_type ?? 'fixed');
        }

        $validated['fee_type'] = $resolvedFeeType;
        $validated['country_code'] = $resolvedCountryCode;
        $validated['fee_amount'] = $resolvedFeeType === 'percentage'
            ? ($validated['fee_percentage'] ?? 0)
            : ($validated['fee_amount'] ?? 0);

        $fee = ($type === 'receipt') ? ReceiptFee::findOrFail($id) : SendingFee::findOrFail($id);
        $fee->update($validated);

        return back()->with('success', 'Palier de frais mis à jour.');
    }

    /**
     * Supprime un palier de frais.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $type = $request->input('type');
        $fee = ($type === 'receipt') ? ReceiptFee::findOrFail($id) : SendingFee::findOrFail($id);
        $fee->delete();

        return back()->with('success', 'Palier de frais supprimé.');
    }
}
