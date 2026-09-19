<?php

namespace App\Http\Controllers;

use App\Models\Method;
use App\Services\PublicFeeOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicCalculatorController extends Controller
{
    public const FREE_TRIALS = 3;

    /**
     * Calcule l'utilisation actuelle via IP, empreinte d'appareil et session
     */
    private function getUsageTracker(Request $request): array
    {
        $ip = $request->ip() ?: '127.0.0.1';
        $today = now()->format('Y-m-d');
        
        // Empreinte d'appareil persistante (cookie 1 an)
        $deviceToken = $request->cookie('momo_device_uid');
        if (!$deviceToken) {
            $deviceToken = (string) Str::uuid();
        }

        $ipKey = 'pub_calc_ip_' . md5($ip . '_' . $today);
        $deviceKey = 'pub_calc_dev_' . md5($deviceToken . '_' . $today);
        $sessionKey = 'pub_calc_sess_' . $today;

        $ipCount = (int) Cache::get($ipKey, 0);
        $devCount = (int) Cache::get($deviceKey, 0);
        $sessCount = (int) Session::get($sessionKey, 0);

        // Le compteur effectif prend le maximum absolu pour empêcher le contournement
        $effectiveCount = max($ipCount, $devCount, $sessCount);
        $remaining = max(0, self::FREE_TRIALS - $effectiveCount);

        return [
            'count' => $effectiveCount,
            'remaining' => $remaining,
            'device_token' => $deviceToken,
            'ip_key' => $ipKey,
            'device_key' => $deviceKey,
            'session_key' => $sessionKey,
        ];
    }

    /**
     * Affiche le formulaire du calculateur public
     */
    public function index(Request $request)
    {
        $tracker = $this->getUsageTracker($request);

        $response = response()->view('public.calculator', [
            'remaining' => $tracker['remaining'],
            'canCalculate' => $tracker['remaining'] > 0,
            'maxTrials' => self::FREE_TRIALS,
        ]);

        // Attacher le cookie d'appareil persistant s'il n'existe pas encore
        if (!$request->cookie('momo_device_uid')) {
            $response->withCookie(Cookie::make('momo_device_uid', $tracker['device_token'], 525600, '/', null, false, false));
        }

        return $response;
    }

    /**
     * Effectue le calcul et enregistre l'essai de façon inviolable (IP + Device)
     */
    public function calculate(Request $request, PublicFeeOptimizer $optimizer)
    {
        $tracker = $this->getUsageTracker($request);

        if ($tracker['remaining'] <= 0) {
            return redirect()->route('public.calculator')
                ->with('error', 'Vous avez atteint la limite de ' . self::FREE_TRIALS . ' calculs gratuits sur cet appareil ou cette connexion. Créez un compte gratuit pour continuer à optimiser sans limite !');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'country' => 'nullable|string|max:10',
            'type' => 'nullable|string|in:withdrawal,sending',
        ]);

        $amount = (float) $validated['amount'];
        $country = $validated['country'] ?? 'BJ';
        $type = $validated['type'] ?? 'withdrawal';

        // Récupérer les méthodes disponibles actives (PostgreSQL)
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

        // Optimisation multi-niveaux (1 tranche, 2 tranches, 3 tranches)
        $result = $optimizer->optimizeWithdrawal($amount, $methodIds, $country, $type);

        if (isset($result['error'])) {
            return redirect()->route('public.calculator')
                ->with('error', $result['error']);
        }

        // Incrémenter atomiquement et synchroniser l'ensemble des verrous (IP + Device + Session)
        $newCount = $tracker['count'] + 1;
        $endOfDay = now()->endOfDay();
        Cache::put($tracker['ip_key'], $newCount, $endOfDay);
        Cache::put($tracker['device_key'], $newCount, $endOfDay);
        Session::put($tracker['session_key'], $newCount);

        $newRemaining = max(0, self::FREE_TRIALS - $newCount);
        $hasRemaining = $newRemaining > 0;

        // Préparer les options pour la vue (pagination)
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

        $response = response()->view('public.calculator-result', [
            'result' => $result,
            'amount' => $amount,
            'country' => $country,
            'currency' => 'FCFA',
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

        return $response->withCookie(Cookie::make('momo_device_uid', $tracker['device_token'], 525600, '/', null, false, false));
    }

    /**
     * Réinitialiser les essais (pour tests et débogage)
     */
    public function resetTrials(Request $request)
    {
        $tracker = $this->getUsageTracker($request);
        Cache::forget($tracker['ip_key']);
        Cache::forget($tracker['device_key']);
        Session::forget($tracker['session_key']);

        return redirect()->route('public.calculator')
            ->with('success', 'Essais réinitialisés pour cette connexion et cet appareil !');
    }
}