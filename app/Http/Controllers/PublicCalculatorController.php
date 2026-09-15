<?php

namespace App\Http\Controllers;

use App\Models\Method;
use App\Services\PublicFeeOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicCalculatorController extends Controller
{
    private const FREE_TRIALS = 3;

    /**
     * Affiche le formulaire du calculateur public
     */
    public function index()
    {
        // ✅ Clé de session sécurisée : IP + sel de l'application
        $ip = request()->ip();
        $sessionKey = 'public_calculator_trials_' . md5($ip . config('app.key'));

        $trialData = Session::get($sessionKey, [
            'count' => 0,
            'reset_at' => now()->format('Y-m-d'),
        ]);

        // ✅ Réinitialisation quotidienne (commenter pour des essais à vie)
        if ($trialData['reset_at'] !== now()->format('Y-m-d')) {
            $trialData = ['count' => 0, 'reset_at' => now()->format('Y-m-d')];
            Session::put($sessionKey, $trialData);
        }

        $remaining = max(0, self::FREE_TRIALS - $trialData['count']);
        $canCalculate = $remaining > 0;

        return view('public.calculator', [
            'remaining' => $remaining,
            'canCalculate' => $canCalculate,
            'maxTrials' => self::FREE_TRIALS,
        ]);
    }

    /**
     * Effectue le calcul et enregistre l'essai
     */
    public function calculate(Request $request, PublicFeeOptimizer $optimizer)
    {
        $ip = request()->ip();
        $sessionKey = 'public_calculator_trials_' . md5($ip . config('app.key'));

        $trialData = Session::get($sessionKey, [
            'count' => 0,
            'reset_at' => now()->format('Y-m-d'),
        ]);

        // Réinitialisation quotidienne (commenter pour essais à vie)
        if ($trialData['reset_at'] !== now()->format('Y-m-d')) {
            $trialData = ['count' => 0, 'reset_at' => now()->format('Y-m-d')];
            Session::put($sessionKey, $trialData);
        }

        $trialCount = $trialData['count'];
        $remaining = self::FREE_TRIALS - $trialCount;

        if ($remaining <= 0) {
            return redirect()->route('public.calculator')
                ->with('error', 'Vous avez utilisé vos 3 essais gratuits. Créez un compte pour débloquer plus de calculs !');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'country' => 'nullable|string|max:10',
            'type' => 'nullable|string|in:withdrawal,sending',
        ]);

        $amount = (float) $validated['amount'];
        $country = $validated['country'] ?? 'BJ';
        $type = $validated['type'] ?? 'withdrawal';

        // ✅ Récupérer les méthodes disponibles (PostgreSQL)
        $methodIds = Method::whereRaw('is_active = true')
            ->where(function ($query) use ($country) {
                $query->where('country_code', $country)
                      ->orWhereNull('country_code');
            })
            ->pluck('id')
            ->toArray();

        if (empty($methodIds)) {
            return redirect()->route('public.calculator')
                ->with('error', 'Aucune méthode de paiement disponible pour ce pays.');
        }

        $result = $optimizer->optimizeWithdrawal($amount, $methodIds, $country);

        if (isset($result['error'])) {
            return redirect()->route('public.calculator')
                ->with('error', $result['error']);
        }

        // ✅ Incrémenter le compteur d'essais
        Session::put($sessionKey, [
            'count' => $trialCount + 1,
            'reset_at' => now()->format('Y-m-d'),
        ]);

        $newTrialData = Session::get($sessionKey);
        $newRemaining = max(0, self::FREE_TRIALS - $newTrialData['count']);
        $hasRemaining = $newRemaining > 0;

        // ✅ Préparer les options pour la vue (pagination)
        $options = [];
        if (isset($result['best']) && isset($result['alternatives'])) {
            $allOptions = array_merge([$result['best']], $result['alternatives']);
            $perPage = 5;
            $page = max(1, (int) $request->query('page', 1));
            $offset = ($page - 1) * $perPage;
            $paginatedOptions = array_slice($allOptions, $offset, $perPage);

            $options = new LengthAwarePaginator(
                $paginatedOptions,
                count($allOptions),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $bestOption = $result['best'] ?? null;
        $shareMessage = $bestOption
            ? 'Bonjour, je dois ' . ($type === 'sending' ? 'envoyer' : 'recevoir') . ' ' . number_format((int) $amount, 0, ',', ' ') . ' FCFA. Le meilleur choix est ' . $bestOption['label'] . ' avec des frais estimés à ' . number_format((float) $bestOption['fee'], 0, ',', ' ') . ' FCFA.'
            : null;

        return view('public.calculator-result', [
            'result' => $result,
            'amount' => $amount,
            'country' => $country,
            'type' => $type,
            'remaining' => $newRemaining,
            'hasRemaining' => $hasRemaining,
            'maxTrials' => self::FREE_TRIALS,
            'summary' => $result,
            'options' => $options,
            'best_option' => $bestOption,
            'share_message' => $shareMessage,
            'message' => null,
            'can_export' => false,
        ]);
    }

    /**
     * Réinitialiser les essais (pour les tests)
     */
    public function resetTrials()
    {
        $ip = request()->ip();
        $sessionKey = 'public_calculator_trials_' . md5($ip . config('app.key'));
        Session::forget($sessionKey);

        return redirect()->route('public.calculator')
            ->with('success', 'Essais réinitialisés !');
    }
}