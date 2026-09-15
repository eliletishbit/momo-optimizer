<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Liste des abonnements.
     */
    public function index(): View
    {
        $subscriptions = Subscription::with('user')->paginate(20);
        return view('pages.admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Modifie un abonnement.
     */
    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,cancelled,expired',
            'ends_at' => 'nullable|date',
        ]);

        $subscription->update($validated);

        return back()->with('success', 'Abonnement mis à jour.');
    }

    /**
     * Annule un abonnement.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->update(['status' => 'cancelled', 'ends_at' => now()]);
        return back()->with('success', 'Abonnement annulé.');
    }
}
