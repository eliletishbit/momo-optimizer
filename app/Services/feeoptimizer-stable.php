<?php

namespace App\Services;

use App\Models\Method;
use App\Models\ReceiptFee;
use Illuminate\Support\Facades\Cache;
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

        // 🔥 Supprimer le cache pour les tests (à commenter en production)
        // Cache::forget('fee_optimizer:withdrawal:' . md5(implode('-', $methodIds) . ':' . (int) round($amount) . ':' . $countryCode));

        $cacheKey = 'fee_optimizer:withdrawal:' . md5(implode('-', $methodIds) . ':' . (int) round($amount) . ':' . $countryCode);

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($amount, $methodIds, $countryCode) {
            $methods = Method::whereIn('id', $methodIds)
                ->where('country_code', $countryCode)
                ->where('is_active', true)
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
                    'details' => [['network' => $method->name, 'amount' => $amount, 'fee' => $fee]],
                ];
            }

            // 2. Fractionnement optimal sur un seul réseau (bornes supérieures)
            foreach ($methods as $method) {
                $splitResult = $this->optimalSplit($method->receiptFees, $amount, $method->name);
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

            // 3. Combinaisons multi-réseaux (optimales)
            $methodList = $methods->values();
            for ($i = 0; $i < $methodList->count(); $i++) {
                for ($j = $i + 1; $j < $methodList->count(); $j++) {
                    $methodA = $methodList[$i];
                    $methodB = $methodList[$j];
                    $combinedResult = $this->optimalCombinedSplit($methodA->receiptFees, $methodB->receiptFees, $amount, $methodA->name, $methodB->name);
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

            // 🔥 TRI PAR FRAIS CROISSANTS (le cœur du classement)
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

            // ⚠️ SUPPRESSION DU FORÇAGE : plus de favoritisme pour Celtiis
            // Le tri par frais fait naturellement son travail

            if (empty($selected)) {
                return $this->buildErrorResponse('Aucune option d\'optimisation trouvée.');
            }

            $best = $selected[0];
            $worst = $selected[count($selected) - 1];
            $savings = max(0, $worst['fee'] - $best['fee']);

            return [
                'amount' => (int) round($amount),
                'best' => $best,
                'alternatives' => array_slice($selected, 1),
                'savings' => (int) round($savings),
            ];
        });
    }

    /**
     * Fractionnement optimal sur un réseau :
     * on prend la plus grande borne supérieure possible ≤ montant restant,
     * puis on recommence avec le reste.
     * Si le montant est dans une seule tranche, on fractionne en prenant la borne supérieure
     * de cette tranche et le reste dans une tranche inférieure.
     */
    private function optimalSplit($fees, float $amount, string $networkName): ?array
    {
        $fees = $fees->sortBy('max_amount')->values();
        if ($fees->isEmpty()) return null;

        $remaining = $amount;
        $details = [];
        $totalFee = 0;

        // Parcourir les tranches du haut vers le bas
        for ($i = $fees->count() - 1; $i >= 0; $i--) {
            if ($remaining <= 0) break;

            $tier = $fees[$i];
            $max = (float) $tier->max_amount;
            $min = (float) $tier->min_amount;
            $fee = (float) $tier->fee_amount;

            // Si le montant restant est dans cette tranche ET supérieur à la borne supérieure de la tranche inférieure
            if ($remaining >= $min) {
                // Chercher la tranche inférieure pour fractionner si possible
                $lowerTier = null;
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($fees[$j]->max_amount < $remaining) {
                        $lowerTier = $fees[$j];
                        break;
                    }
                }

                if ($lowerTier && $remaining > $lowerTier->max_amount) {
                    // Fractionner : prendre la borne supérieure de la tranche inférieure
                    $part1 = (float) $lowerTier->max_amount;
                    $part2 = $remaining - $part1;
                    if ($part2 >= 100) {
                        $fee1 = (float) $lowerTier->fee_amount;
                        $fee2 = $this->getFeeForAmount($fees, $part2);
                        $details = [
                            ['network' => $networkName, 'amount' => $part1, 'fee' => $fee1],
                            ['network' => $networkName, 'amount' => $part2, 'fee' => $fee2],
                        ];
                        return ['fee' => $fee1 + $fee2, 'details' => $details];
                    }
                }

                // Si pas de fractionnement possible, prendre le montant dans cette tranche
                $toTake = min($max, $remaining);
                if ($toTake >= $min && $toTake > 0) {
                    $details[] = ['network' => $networkName, 'amount' => $toTake, 'fee' => $fee];
                    $totalFee += $fee;
                    $remaining -= $toTake;
                }
            }
        }

        // Si un reste persiste, on le met dans la première tranche
        if ($remaining > 0 && $fees->count() > 0) {
            $firstTier = $fees->first();
            $details[] = [
                'network' => $networkName,
                'amount' => $remaining,
                'fee' => (float) $firstTier->fee_amount
            ];
            $totalFee += (float) $firstTier->fee_amount;
        }

        return ['fee' => $totalFee, 'details' => $details];
    }

    /**
     * Combinaison optimale entre deux réseaux :
     * on essaye de prendre une borne supérieure sur le réseau A, le reste sur le réseau B.
     */
    private function optimalCombinedSplit($feesA, $feesB, float $amount, string $nameA, string $nameB): ?array
    {
        $feesA = $feesA->sortBy('max_amount')->values();
        $feesB = $feesB->sortBy('max_amount')->values();

        if ($feesA->isEmpty() || $feesB->isEmpty()) return null;

        $bestResult = null;
        $bestFee = PHP_FLOAT_MAX;

        // Tester A en premier
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
                                ['network' => $nameA, 'amount' => $partA, 'fee' => $feeA],
                                ['network' => $nameB, 'amount' => $partB, 'fee' => $feeB],
                            ],
                        ];
                    }
                }
            }
        }

        // Tester B en premier
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
                                ['network' => $nameA, 'amount' => $partA, 'fee' => $feeA],
                                ['network' => $nameB, 'amount' => $partB, 'fee' => $feeB],
                            ],
                        ];
                    }
                }
            }
        }

        return $bestResult;
    }

    private function getFeeForAmount($fees, float $amount): float
    {
        foreach ($fees as $tier) {
            if ($amount >= $tier->min_amount && $amount <= $tier->max_amount) {
                return (float) $tier->fee_amount;
            }
        }
        return 0.0;
    }

    private function formatOption(array $option): array
    {
        return [
            'label' => $option['label'],
            'fee' => (float) $option['fee'],
            'net' => (float) $option['net'],
            'details' => $option['details'],
        ];
    }

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