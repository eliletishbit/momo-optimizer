<?php

namespace App\Services;

use App\Models\Method;
use App\Models\OptimizationHistory;
use App\Models\ReceiptFee;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class FeeOptimizer
{
    public function optimizeWithdrawal(float $amount, array $userMethodIds, string $countryCode = 'BJ'): array
    {
        $amount = (float) $amount;

        if ($amount < 100) {
            return $this->buildErrorResponse('Montant minimum : 100 FCFA.');
        }

        $methodIds = array_values(array_unique(array_filter(array_map('strval', $userMethodIds))));
        if (empty($methodIds)) {
            return $this->buildErrorResponse('Aucune méthode de paiement sélectionnée.');
        }

        $cacheKey = 'fee_optimizer:withdrawal:' . md5(implode('-', $methodIds) . ':' . (int) round($amount) . ':' . $countryCode);

        // ✅ VÉRIFIER SI LE CACHE EXISTE DÉJÀ
        $cachedResult = Cache::get($cacheKey);

        if ($cachedResult) {
            // ✅ Le cache existe : on le retourne directement
            // ✅ MAIS ON FAIT QUAND MÊME LA SAUVEGARDE !
            $this->saveOptimizationFromResult($amount, $cachedResult, $countryCode);
            return $cachedResult;
        }

        // ✅ Cache MISS : on calcule, sauvegarde ET on met en cache
        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($amount, $methodIds, $countryCode) {
            $methods = Method::whereIn('id', $methodIds)
                ->where('country_code', $countryCode)
                ->whereRaw('is_active = true')
                ->with(['receiptFees' => function ($query) use ($countryCode) {
                    $query->where('country_code', $countryCode)->orderBy('max_amount');
                }])
                ->orderBy('name')
                ->get();

            if ($methods->isEmpty()) {
                return $this->buildErrorResponse('Aucune méthode disponible pour ce pays.');
            }

            $allOptions = [];

            // 1. Réseau seul (sans fractionnement)
            foreach ($methods as $method) {
                $fee = $this->getFeeForAmount($method->receiptFees, $amount);
                $allOptions[] = [
                    'type' => 'single',
                    'label' => $method->name . ' seul',
                    'fee' => $fee,
                    'net' => $amount - $fee,
                    'details' => [['network' => $method->name, 'amount' => $amount, 'fee' => $fee, 'method_id' => $method->id]],
                ];
            }

            // 2. Fractionnement optimal sur un seul réseau
            foreach ($methods as $method) {
                $splitResult = $this->optimalSplit($method->receiptFees, $amount, $method->name, $method->id);
                if ($splitResult) {
                    $allOptions[] = [
                        'type' => 'optimal_split',
                        'label' => $method->name . ' fractionné (optimal)',
                        'fee' => $splitResult['fee'],
                        'net' => $amount - $splitResult['fee'],
                        'details' => $splitResult['details'],
                    ];
                }
            }

            // 3. Combinaisons multi-réseaux
            $methodList = $methods->values();
            for ($i = 0; $i < $methodList->count(); $i++) {
                for ($j = $i + 1; $j < $methodList->count(); $j++) {
                    $methodA = $methodList[$i];
                    $methodB = $methodList[$j];
                    $combinedResult = $this->optimalCombinedSplit(
                        $methodA->receiptFees, 
                        $methodB->receiptFees, 
                        $amount, 
                        $methodA->name, 
                        $methodB->name, 
                        $methodA->id, 
                        $methodB->id
                    );
                    if ($combinedResult) {
                        $allOptions[] = [
                            'type' => 'combined',
                            'label' => $methodA->name . ' + ' . $methodB->name,
                            'fee' => $combinedResult['fee'],
                            'net' => $amount - $combinedResult['fee'],
                            'details' => $combinedResult['details'],
                        ];
                    }
                }
            }

            // 4. Fractionnement en 3 tranches (ultra-optimal)
            foreach ($methods as $method) {
                $threeSplit = $this->optimalThreeSplit($method->receiptFees, $amount, $method->name, $method->id);
                if ($threeSplit) {
                    $allOptions[] = [
                        'type' => 'three_tier',
                        'label' => $method->name . ' en 3 tranches (ultra-optimisé)',
                        'fee' => $threeSplit['fee'],
                        'net' => $amount - $threeSplit['fee'],
                        'details' => $threeSplit['details'],
                    ];
                }
            }

            // TRI PAR FRAIS CROISSANTS
            usort($allOptions, fn($a, $b) => $a['fee'] <=> $b['fee']);

            // Sélectionner les 12 meilleures (sans doublon de label)
            $selected = [];
            $usedLabels = [];
            foreach ($allOptions as $opt) {
                if (count($selected) >= 12) break;
                if (!in_array($opt['label'], $usedLabels)) {
                    $selected[] = $this->formatOption($opt);
                    $usedLabels[] = $opt['label'];
                }
            }

            if (empty($selected)) {
                return $this->buildErrorResponse('Aucune option d\'optimisation trouvée.');
            }

            $best = $selected[0];
            $worst = $selected[count($selected) - 1];
            
            // ✅ Calcul de l'économie RÉELLE (différence entre le pire et le meilleur)
            $savings = max(0, $worst['fee'] - $best['fee']);

            // ✅ SAUVEGARDE AVEC LE VRAI SAVINGS
            $this->saveOptimization($amount, $best, array_slice($selected, 1), $countryCode, (int) round($savings));

            return [
                'amount' => (int) round($amount),
                'best' => $best,
                'alternatives' => array_slice($selected, 1),
                'savings' => (int) round($savings),
            ];
        });

        return $result;
    }

    /**
     * ✅ Sauvegarde à partir d'un résultat en cache (CACHE HIT)
     */
    private function saveOptimizationFromResult(float $amount, array $result, string $countryCode): void
    {
        try {
            // Ne pas sauvegarder s'il y a une erreur
            if (isset($result['error']) || !isset($result['best'])) {
                return;
            }

            $user = Auth::user();
            if (!$user instanceof \App\Models\User) {
                return;
            }

            $methodId = $result['best']['details'][0]['method_id'] ?? null;
            
            // ✅ Vérifier les doublons (même montant + même méthode dans les 5 min)
            $existing = OptimizationHistory::where('user_id', $user->id)
                ->where('amount', $amount)
                ->where('selected_method_id', $methodId)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($existing) {
                Log::info('⏭️ Sauvegarde ignorée (doublon récent)', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'method_id' => $methodId,
                ]);
                return;
            }

            // ✅ CORRECTION : utiliser le vrai savings du résultat
            $savings = $result['savings'] ?? 0;

            OptimizationHistory::create([
                'user_id' => $user->id,
                'type' => 'receipt',
                'amount' => $amount,
                'selected_method_id' => $methodId,
                'total_fee' => $result['best']['fee'] ?? 0,
                'savings' => $savings,
                'alternatives' => $result['alternatives'] ?? [],
                'country_code' => $countryCode,
            ]);

            Log::info('✅ Historique sauvegardé (cache HIT)', [
                'user_id' => $user->id,
                'amount' => $amount,
                'savings' => $savings,
                'fee' => $result['best']['fee'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur sauvegarde (cache HIT): ' . $e->getMessage());
        }
    }

    /**
     * ✅ Sauvegarde dans l'historique (CACHE MISS)
     */
    private function saveOptimization(float $amount, array $best, array $alternatives, string $countryCode, int $savings): void
    {
        try {
            $user = Auth::user();
            if (!$user instanceof \App\Models\User) {
                Log::warning('⚠️ Sauvegarde ignorée : utilisateur non connecté');
                return;
            }

            $methodId = $best['details'][0]['method_id'] ?? null;

            // ✅ CORRECTION : utiliser le vrai savings passé en paramètre
            OptimizationHistory::create([
                'user_id' => $user->id,
                'type' => 'receipt',
                'amount' => $amount,
                'selected_method_id' => $methodId,
                'total_fee' => $best['fee'] ?? 0,
                'savings' => $savings,
                'alternatives' => $alternatives,
                'country_code' => $countryCode,
            ]);

            Log::info('✅ Historique sauvegardé (cache MISS)', [
                'user_id' => $user->id,
                'amount' => $amount,
                'savings' => $savings,
                'fee' => $best['fee'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur sauvegarde (cache MISS): ' . $e->getMessage());
        }
    }

    /**
     * Fractionnement optimal sur un seul réseau
     */
    private function optimalSplit($fees, float $amount, string $networkName, string $methodId): ?array
    {
        $fees = $fees->sortBy('max_amount')->values();
        if ($fees->isEmpty()) return null;

        $remaining = $amount;
        $details = [];
        $totalFee = 0;

        for ($i = $fees->count() - 1; $i >= 0; $i--) {
            if ($remaining <= 0) break;

            $tier = $fees[$i];
            $max = (float) $tier->max_amount;
            $min = (float) $tier->min_amount;
            $fee = (float) $tier->fee_amount;

            if ($remaining >= $min) {
                $lowerTier = null;
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($fees[$j]->max_amount < $remaining) {
                        $lowerTier = $fees[$j];
                        break;
                    }
                }

                if ($lowerTier && $remaining > $lowerTier->max_amount) {
                    $part1 = (float) $lowerTier->max_amount;
                    $part2 = $remaining - $part1;
                    if ($part2 >= 100) {
                        $fee1 = (float) $lowerTier->fee_amount;
                        $fee2 = $this->getFeeForAmount($fees, $part2);
                        $details = [
                            ['network' => $networkName, 'amount' => $part1, 'fee' => $fee1, 'method_id' => $methodId],
                            ['network' => $networkName, 'amount' => $part2, 'fee' => $fee2, 'method_id' => $methodId],
                        ];
                        return ['fee' => $fee1 + $fee2, 'details' => $details];
                    }
                }

                $toTake = min($max, $remaining);
                if ($toTake >= $min && $toTake > 0) {
                    $details[] = ['network' => $networkName, 'amount' => $toTake, 'fee' => $fee, 'method_id' => $methodId];
                    $totalFee += $fee;
                    $remaining -= $toTake;
                }
            }
        }

        if ($remaining > 0 && $fees->count() > 0) {
            $firstTier = $fees->first();
            $details[] = [
                'network' => $networkName,
                'amount' => $remaining,
                'fee' => (float) $firstTier->fee_amount,
                'method_id' => $methodId
            ];
            $totalFee += (float) $firstTier->fee_amount;
        }

        return ['fee' => $totalFee, 'details' => $details];
    }

    /**
     * Combinaison optimale entre deux réseaux
     */
    private function optimalCombinedSplit($feesA, $feesB, float $amount, string $nameA, string $nameB, string $idA, string $idB): ?array
    {
        $feesA = $feesA->sortBy('max_amount')->values();
        $feesB = $feesB->sortBy('max_amount')->values();

        if ($feesA->isEmpty() || $feesB->isEmpty()) return null;

        $bestResult = null;
        $bestFee = PHP_FLOAT_MAX;

        foreach ($feesA as $tierA) {
            $maxA = (float) $tierA->max_amount;
            $minA = (float) $tierA->min_amount;
            if ($maxA < $amount && $maxA >= $minA) {
                $partA = $maxA;
                $partB = $amount - $partA;
                if ($partB >= 100) {
                    $feeA = (float) $tierA->fee_amount;
                    $feeB = $this->getFeeForAmount($feesB, $partB);
                    $totalFee = $feeA + $feeB;
                    if ($totalFee < $bestFee) {
                        $bestFee = $totalFee;
                        $bestResult = [
                            'fee' => $totalFee,
                            'details' => [
                                ['network' => $nameA, 'amount' => $partA, 'fee' => $feeA, 'method_id' => $idA],
                                ['network' => $nameB, 'amount' => $partB, 'fee' => $feeB, 'method_id' => $idB],
                            ],
                        ];
                    }
                }
            }
        }

        foreach ($feesB as $tierB) {
            $maxB = (float) $tierB->max_amount;
            $minB = (float) $tierB->min_amount;
            if ($maxB < $amount && $maxB >= $minB) {
                $partB = $maxB;
                $partA = $amount - $partB;
                if ($partA >= 100) {
                    $feeB = (float) $tierB->fee_amount;
                    $feeA = $this->getFeeForAmount($feesA, $partA);
                    $totalFee = $feeA + $feeB;
                    if ($totalFee < $bestFee) {
                        $bestFee = $totalFee;
                        $bestResult = [
                            'fee' => $totalFee,
                            'details' => [
                                ['network' => $nameA, 'amount' => $partA, 'fee' => $feeA, 'method_id' => $idA],
                                ['network' => $nameB, 'amount' => $partB, 'fee' => $feeB, 'method_id' => $idB],
                            ],
                        ];
                    }
                }
            }
        }

        return $bestResult;
    }

    /**
     * Fractionnement optimal en 3 tranches sur un seul réseau
     */
    private function optimalThreeSplit($fees, float $amount, string $networkName, string $methodId): ?array
    {
        $fees = $fees->sortBy('max_amount')->values();
        if ($fees->isEmpty() || $amount < 300) return null;

        $singleFee = $this->getFeeForAmount($fees, $amount);
        $bestFee = $singleFee;
        $bestDetails = null;

        // 1. Découpage en 3 tiers égaux
        $third = round($amount / 3);
        $rem = $amount - (2 * $third);
        if ($third >= 100 && $rem >= 100) {
            $f1 = $this->getFeeForAmount($fees, $third);
            $f2 = $this->getFeeForAmount($fees, $third);
            $f3 = $this->getFeeForAmount($fees, $rem);
            $tot = $f1 + $f2 + $f3;
            if ($tot < $bestFee) {
                $bestFee = $tot;
                $bestDetails = [
                    ['network' => $networkName, 'amount' => $third, 'fee' => $f1, 'method_id' => $methodId],
                    ['network' => $networkName, 'amount' => $third, 'fee' => $f2, 'method_id' => $methodId],
                    ['network' => $networkName, 'amount' => $rem, 'fee' => $f3, 'method_id' => $methodId],
                ];
            }
        }

        // 2. Découpage combinant des seuils de paliers
        $tierMaxes = $fees->pluck('max_amount')->filter(fn($m) => $m > 0 && $m < $amount)->values()->all();
        $tierCount = count($tierMaxes);

        for ($i = 0; $i < $tierCount; $i++) {
            $t1 = (float) $tierMaxes[$i];
            for ($j = $i; $j < $tierCount; $j++) {
                $t2 = (float) $tierMaxes[$j];
                if ($t1 + $t2 < $amount) {
                    $t3 = $amount - $t1 - $t2;
                    if ($t3 >= 100) {
                        $f1 = $this->getFeeForAmount($fees, $t1);
                        $f2 = $this->getFeeForAmount($fees, $t2);
                        $f3 = $this->getFeeForAmount($fees, $t3);
                        $tot = $f1 + $f2 + $f3;
                        if ($tot < $bestFee) {
                            $bestFee = $tot;
                            $bestDetails = [
                                ['network' => $networkName, 'amount' => $t1, 'fee' => $f1, 'method_id' => $methodId],
                                ['network' => $networkName, 'amount' => $t2, 'fee' => $f2, 'method_id' => $methodId],
                                ['network' => $networkName, 'amount' => $t3, 'fee' => $f3, 'method_id' => $methodId],
                            ];
                        }
                    }
                }
            }
        }

        if ($bestDetails && $bestFee < $singleFee) {
            return ['fee' => $bestFee, 'details' => $bestDetails];
        }

        return null;
    }

    /**
     * Calcule les frais pour un montant donné sur un ensemble de paliers
     */
    private function getFeeForAmount($fees, float $amount): float
    {
        foreach ($fees as $tier) {
            if ($amount >= $tier->min_amount && $amount <= $tier->max_amount) {
                return (float) $tier->fee_amount;
            }
        }
        return 0.0;
    }

    /**
     * Formate une option
     */
    private function formatOption(array $option): array
    {
        return [
            'label' => $option['label'],
            'fee' => (float) $option['fee'],
            'net' => (float) $option['net'],
            'details' => $option['details'],
        ];
    }

    /**
     * Construit une réponse d'erreur
     */
    private function buildErrorResponse(string $message): array
    {
        return [
            'error' => $message,
            'amount' => 0,
            'best' => null,
            'alternatives' => [],
            'savings' => 0,
        ];
    }
}