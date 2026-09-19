<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OptimizationHistory;
use App\Models\Method;
use App\Services\FeeOptimizer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $startTime = microtime(true);
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $methodCount = Cache::remember("user_methods_count_{$user->id}", now()->addMinutes(15), function () use ($user) {
            return $user->userMethods()->count();
        });

        if (! $user->canAccessCalculator()) {
            if ($user->subscription === 'pay_as_you_go' && $user->payg_credits <= 0) {
                return redirect()->route('pricing')->with('error', 
                    'Vous avez utilisé tous vos crédits Pay As You Go.'
                );
            }

            if ($user->isDegraded()) {
                $remaining = $user->getRemainingCalculatorUsesThisMonth();
                $nextMonth = $user->getNextMonthlyRenewalDate();
                
                if ($remaining <= 0) {
                    return redirect()->route('pricing')->with('error', 
                        "Vous avez utilisé votre unique calcul gratuit du mois. Prochain calcul disponible le {$nextMonth}."
                    );
                }
                
                return redirect()->route('pricing')->with('warning', 
                    "Mode dégradé - 1 calcul gratuit ce mois-ci."
                );
            }

            if ($user->hasExpiredFreeTrial()) {
                return redirect()->route('pricing')->with('error', 
                    'Votre essai gratuit a expiré.'
                );
            }
            
            if ($user->hasUsedFreeTrial() && $user->subscription === 'free') {
                return redirect()->route('pricing')->with('error', 
                    'Votre essai gratuit est terminé.'
                );
            }
            
            return redirect()->route('pricing')->with('error', 
                'Veuillez choisir une formule pour accéder au calculateur.'
            );
        }

        $daysLeft = $user->getFreeTrialDaysLeft();
        if ($daysLeft !== null && $daysLeft <= 3 && $user->subscription === 'free') {
            session()->flash('warning', "⚠️ Votre essai gratuit expire dans {$daysLeft} jours.");
        }

        $executionTime = round(microtime(true) - $startTime, 3);
        Log::info('⏱️ Page calculateur chargée en ' . $executionTime . ' secondes', [
            'user_id' => $user->id,
            'method_count' => $methodCount,
            'subscription_status' => $user->getSubscriptionStatus(),
        ]);

        return view('pages.calculator', [
            'subscription_status' => $user->getSubscriptionStatus(),
            'trial_days_left' => $daysLeft,
        ]);
    }

    public function calculate(Request $request, FeeOptimizer $optimizer): View|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->result($request, $optimizer);
    }

    public function result(Request $request, FeeOptimizer $optimizer): View|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $startTime = microtime(true);
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        Log::info('🚀 [START] result() appelé', ['user_id' => $user->id]);

        if (! $user->canAccessCalculator()) {
            if ($user->subscription === 'pay_as_you_go' && $user->payg_credits <= 0) {
                return redirect()->route('pricing')->with('error', 'Crédits épuisés.');
            }

            if ($user->isDegraded()) {
                $remaining = $user->getRemainingCalculatorUsesThisMonth();
                if ($remaining <= 0) {
                    return redirect()->route('pricing')->with('error', 'Limite mensuelle atteinte.');
                }
            }

            if ($user->hasExpiredFreeTrial()) {
                return redirect()->route('pricing')->with('error', 'Essai gratuit expiré.');
            }
            
            if ($user->hasUsedFreeTrial() && $user->subscription === 'free') {
                return redirect()->route('pricing')->with('error', 'Essai gratuit terminé.');
            }
            
            return redirect()->route('pricing')->with('error', 'Veuillez choisir une formule pour continuer.');
        }

        if ($user->isDegraded()) {
            $remaining = $user->getRemainingCalculatorUsesThisMonth();
            if ($remaining <= 0) {
                return redirect()->route('pricing')->with('error', 'Limite mensuelle atteinte.');
            }
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'country' => 'nullable|string|max:10',
            'type' => 'nullable|string|in:withdrawal,sending',
        ]);

        $amount = (float) $validated['amount'];
        $selectedCountry = $validated['country'] ?? $user->country_code ?? 'BJ';
        $type = $validated['type'] ?? 'withdrawal';

        Log::info('📊 [PARAMS]', [
            'amount' => $amount,
            'country' => $selectedCountry,
            'type' => $type,
            'user_id' => $user->id,
        ]);

        $userMethodIds = Cache::remember("user_method_ids_{$user->id}_{$selectedCountry}", now()->addMinutes(15), function () use ($user, $selectedCountry) {
            return $user->userMethods()
                ->whereHas('method', function ($query) use ($selectedCountry) {
                    $query->where(function ($q) use ($selectedCountry) {
                        $q->where('country_code', $selectedCountry)
                          ->orWhereNull('country_code');
                    });
                })
                ->pluck('method_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        });

        // Fallback automatique sur les méthodes actives du pays si l'utilisateur n'en a pas configuré
        if (empty($userMethodIds)) {
            $userMethodIds = Method::where('country_code', $selectedCountry)
                ->whereRaw('is_active = true')
                ->pluck('id')
                ->toArray();
        }

        if (empty($userMethodIds)) {
            $userMethodIds = Method::whereRaw('is_active = true')
                ->pluck('id')
                ->toArray();
        }

        if (empty($userMethodIds)) {
            return redirect()->route('settings')->with('error', 
                'Aucun moyen de paiement disponible pour ce pays.'
            );
        }

        Log::info('🔍 [OPTIMIZE] Appel de optimizeWithdrawal', [
            'amount' => $amount,
            'method_count' => count($userMethodIds),
            'country' => $selectedCountry,
        ]);

        $result = $optimizer->optimizeWithdrawal($amount, $userMethodIds, $selectedCountry);

        Log::info('📦 [RESULT] Résultat reçu', [
            'keys' => array_keys($result),
            'has_error' => isset($result['error']),
            'has_best' => isset($result['best']),
            'fallback' => $result['fallback'] ?? false,
            'savings' => $result['savings'] ?? 'N/A',
        ]);

        if (isset($result['error'])) {
            Log::warning('⚠️ [ERROR]', ['error' => $result['error']]);

            return view('pages.calculator.result', [
                'amount' => $amount,
                'country' => $selectedCountry,
                'type' => $type,
                'options' => new LengthAwarePaginator([], 0, 5, 1),
                'summary' => null,
                'message' => $result['error'],
                'best_option' => null,
                'share_message' => null,
                'can_export' => $user->hasActivePremium() || $user->hasActivePro() || $user->isAdmin(),
                'trial_days_left' => $user->getFreeTrialDaysLeft(),
                'subscription_status' => $user->getSubscriptionStatus(),
                'subscription_message' => $user->getSubscriptionMessage(),
                'remaining_uses_this_month' => $user->getRemainingCalculatorUsesThisMonth(),
                'execution_time' => round(microtime(true) - $startTime, 3),
            ]);
        }

        // ✅ SI L'UTILISATEUR EST EN MODE DÉGRADÉ
        if ($user->isDegraded()) {
            $user->incrementCalculatorUse();
            $remaining = $user->getRemainingCalculatorUsesThisMonth();
            session()->flash('info', "🔍 Calcul utilisé. Il vous reste " . $remaining . " calcul gratuit ce mois-ci.");
        }

        // ✅ Export CSV
        if ($request->boolean('download')) {
            if (!$user->hasActivePremium() && !$user->hasActivePro() && !$user->isAdmin()) {
                return redirect()->back()->with('error', 'L\'export CSV est réservé aux abonnés Premium et Pro.');
            }

            $csvHeaders = ['Rang', 'Option', 'Frais total', 'Montant à recevoir', 'Détails'];
            $csvRows = [];

            $allOptions = collect([$result['best'], ...$result['alternatives']]);
            foreach ($allOptions as $index => $option) {
                $csvRows[] = [
                    $index + 1,
                    $option['label'],
                    $option['fee'],
                    (float) (($option['net'] ?? 0) + ($option['fee'] ?? 0)),
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

        // ✅ PAGINATION
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

        $canExport = $user->hasActivePremium() || $user->hasActivePro() || $user->isAdmin();
        $remainingUses = $user->isDegraded() ? $user->getRemainingCalculatorUsesThisMonth() : PHP_INT_MAX;

        // ================================================================
        // ✅ SAUVEGARDE - AVEC LOGS DÉTAILLÉS
        // ================================================================
        
        Log::info('💾 [SAVE] Tentative de sauvegarde', [
            'user_id' => $user->id,
            'amount' => $amount,
            'has_best' => isset($result['best']),
            'has_error' => isset($result['error']),
        ]);

        try {
            if (isset($result['best']) && !isset($result['error'])) {
                
                // Récupérer l'ID de la méthode
                $methodId = null;
                if (isset($result['best']['details'][0]['method_id'])) {
                    $methodId = $result['best']['details'][0]['method_id'];
                } elseif (isset($result['best']['details'][0]['network'])) {
                    $networkName = $result['best']['details'][0]['network'];
                    $method = Method::where('name', $networkName)->first();
                    $methodId = $method->id ?? null;
                }

                $data = [
                    'user_id' => $user->id,
                    'type' => $type === 'withdrawal' ? 'receipt' : 'send',
                    'amount' => $amount,
                    'selected_method_id' => $methodId,
                    'total_fee' => $result['best']['fee'] ?? 0,
                    'savings' => $result['savings'] ?? 0,
                    'alternatives' => $result['alternatives'] ?? [],
                ];

                Log::info('📝 [SAVE] Données préparées', $data);

                // ✅ INSERTION
                $history = OptimizationHistory::create($data);
                
                Log::info('✅ [SAVE] SUCCÈS ! ID: ' . $history->id, [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'savings' => $result['savings'] ?? 0,
                ]);
                
                // Vider le cache
                Cache::forget("user_optimizations_count_{$user->id}");
                Cache::forget("user_total_savings_{$user->id}");
                Cache::forget("user_stats_{$user->id}");
                Cache::forget("dashboard_stats_{$user->id}");
                
                Log::info('🗑️ [SAVE] Cache vidé');
                
            } else {
                Log::warning('⚠️ [SAVE] Ignorée - pas de "best" ou erreur', [
                    'has_best' => isset($result['best']),
                    'has_error' => isset($result['error']),
                    'error' => $result['error'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ [SAVE] ERREUR CRITIQUE: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info('✅ [END] Calcul terminé', [
            'user_id' => $user->id,
            'amount' => $amount,
            'execution_time' => round(microtime(true) - $startTime, 3),
        ]);

        return view('pages.calculator.result', [
            'amount' => $amount,
            'country' => $selectedCountry,
            'type' => $type,
            'options' => $paginator,
            'summary' => $result,
            'message' => null,
            'best_option' => $bestOption,
            'share_message' => $shareMessage,
            'can_export' => $canExport,
            'trial_days_left' => $user->getFreeTrialDaysLeft(),
            'subscription_status' => $user->getSubscriptionStatus(),
            'subscription_message' => $user->getSubscriptionMessage(),
            'remaining_uses_this_month' => $remainingUses,
            'next_renewal_date' => $user->getNextMonthlyRenewalDate(),
            'execution_time' => round(microtime(true) - $startTime, 3),
            'is_fallback' => $result['fallback'] ?? false,
        ]);
    }
}