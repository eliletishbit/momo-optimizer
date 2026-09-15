<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FeeOptimizer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    /**
     * Affiche le formulaire du calculateur.
     */
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if ($user->hasExpiredFreeTrial()) {
            return redirect()->route('pricing')->with('error', 'Votre essai gratuit a expiré. Souscrivez à un forfait premium, pro ou pay as you go pour continuer.');
        }

        if ($user->userMethods()->count() < 2) {
            return redirect()->route('settings')->with('error', 'Ajoutez au moins 2 moyens de paiement pour utiliser le calculateur d’optimisation.');
        }

        return view('pages.calculator');
    }

    /**
     * Traite le montant et affiche les résultats de l'optimisation.
     */
    public function result(Request $request, FeeOptimizer $optimizer): View|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if ($user->hasExpiredFreeTrial()) {
            return redirect()->route('pricing')->with('error', 'Votre essai gratuit a expiré. Souscrivez à un forfait premium, pro ou pay as you go pour continuer.');
        }

        if ($user->userMethods()->count() < 2) {
            return redirect()->route('settings')->with('error', 'Ajoutez au moins 2 moyens de paiement pour utiliser le calculateur d’optimisation.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'country' => 'nullable|string|max:10',
            'type' => 'nullable|string|in:withdrawal,sending',
        ]);

        $amount = (float) $validated['amount'];
        $country = $validated['country'] ?? $user->country_code ?? 'BJ';
        $type = $validated['type'] ?? 'withdrawal';

        $userMethodIds = $user->userMethods()->pluck('method_id')->toArray();
        $result = $optimizer->optimizeWithdrawal($amount, $userMethodIds,$user->country_code ?? 'BJ' );

        if (isset($result['error'])) {
            return view('pages.calculator.result', [
                'amount' => $amount,
                'country' => $country,
                'type' => $type,
                'options' => new LengthAwarePaginator([], 0, 5, 1),
                'summary' => null,
                'message' => $result['error'],
                'best_option' => null,
                'share_message' => null,
                'can_export' => $user->hasActiveSubscription() || $user->subscription === 'free',
            ]);
        }

        if ($request->boolean('download')) {
            $csvHeaders = ['Rang', 'Option', 'Frais total', 'Montant net', 'Détails'];
            $csvRows = [];

            foreach ($result['alternatives'] as $index => $option) {
                $csvRows[] = [
                    $index + 1,
                    $option['label'],
                    $option['fee'],
                    $option['net'],
                    implode(' | ', array_map(fn ($item) => $item['network'] . ':' . $item['amount'] . '(' . $item['fee'] . ')', $option['details'])),
                ];
            }

            $stream = fopen('php://temp', 'r+');
            fputcsv($stream, $csvHeaders);
            foreach ($csvRows as $row) {
                fputcsv($stream, $row);
            }
            rewind($stream);

            return response()->streamDownload(function () use ($stream) {
                while (! feof($stream)) {
                    echo fread($stream, 8192);
                }
                fclose($stream);
            }, 'momoopti-' . $type . '-' . (int) $amount . '.csv');
        }

        $items = collect([$result['best'], ...$result['alternatives']]);
        $perPage = 5;
        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $bestOption = $paginator->items()[0] ?? $result['best'];
        $shareMessage = $bestOption
            ? 'Bonjour, je dois ' . ($type === 'sending' ? 'envoyer' : 'recevoir') . ' ' . number_format((int) $amount, 0, ',', ' ') . ' FCFA. Le meilleur choix est ' . $bestOption['label'] . ' avec des frais estimés à ' . number_format((float) $bestOption['fee'], 0, ',', ' ') . ' FCFA.'
            : null;

        return view('pages.calculator.result', [
            'amount' => $amount,
            'country' => $country,
            'type' => $type,
            'options' => $paginator,
            'summary' => $result,
            'message' => null,
            'best_option' => $bestOption,
            'share_message' => $shareMessage,
            'can_export' => $user->hasActiveSubscription() || $user->subscription === 'free',
        ]);
    }
}
