<?php

namespace App\Services;

use App\Models\Method;
use App\Models\ReceiptFee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeeOptimizer
{
    public function optimizeWithdrawal(float $amount, array $userMethodIds, string $countryCode = 'BJ'): array
    {
        Log::info('📊 FeeOptimizer classique appelé', [
            'amount' => $amount,
            'country' => $countryCode,
        ]);

        $amount = (float) $amount;

        if ($amount < 100) {
            return $this->buildErrorResponse('Montant minimum : 100 FCFA.');
        }

        $methodIds = array_values(array_unique(array_filter(array_map('strval', $userMethodIds))));
        if (empty($methodIds)) {
            return $this->buildErrorResponse('Aucune méthode de paiement sélectionnée.');
        }

        $cacheKey = 'fee_optimizer:withdrawal:' . md5(implode('-', $methodIds) . ':' . (int) round($amount) . ':' . $countryCode);

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($amount, $methodIds, $countryCode) {

            $methods = Method::whereIn('id', $methodIds)
                ->whereRaw('is_active = true')
                ->whereRaw('(country_code = ? OR country_code IS NULL)', [$countryCode])
                ->with(['receiptFees' => function ($query) use ($countryCode) {
                    $query->whereRaw('(country_code = ? OR country_code IS NULL)', [$countryCode])
                        ->orderBy('max_amount');
                }])
                ->orderBy('name')
                ->get();

            if ($methods->isEmpty()) {
                return $this->buildErrorResponse('Aucune méthode disponible pour ce pays.');
            }

            // === 1. Générer les options mono-réseau ===
            $monoOptions = [];

            foreach ($methods as $method) {
                // Option "réseau seul"
                $singleFee = $this->getFeeForAmount($method->receiptFees, $amount);
                $monoOptions[] = [
                    'type' => 'single',
                    'label' => $method->name . ' seul',
                    'fee' => $singleFee,
                    'net' => $amount - $singleFee,
                    'details' => [['network' => $method->name, 'amount' => $amount, 'fee' => $singleFee]],
                ];

                // Option "fractionné optimal" (décomposition par bornes supérieures)
                $splitResult = $this->optimalSplit($method->receiptFees, $amount, $method->name);
                if ($splitResult && $splitResult['fee'] < $singleFee) {
                    $monoOptions[] = [
                        'type' => 'optimal_split',
                        'label' => $method->name . ' fractionné (optimal)',
                        'fee' => $splitResult['fee'],
                        'net' => $amount - $splitResult['fee'],
                        'details' => $splitResult['details'],
                    ];
                }
            }

            // === 2. Générer les options multi-réseaux (combinaisons de 2 réseaux) ===
            $multiOptions = [];
            $methodList = $methods->values();
            for ($i = 0; $i < $methodList->count(); $i++) {
                for ($j = $i + 1; $j < $methodList->count(); $j++) {
                    $methodA = $methodList[$i];
                    $methodB = $methodList[$j];
                    // ✅ PASSER LES DEUX MÉTHODES EN PARAMÈTRE
                    $combinedResult = $this->optimalCombinedSplit(
                        $methodA->receiptFees,
                        $methodB->receiptFees,
                        $amount,
                        $methodA->name,
                        $methodB->name,
                        $methodA,
                        $methodB
                    );
                    if ($combinedResult) {
                        $multiOptions[] = [
                            'type' => 'combined',
                            'label' => $methodA->name . ' + ' . $methodB->name,
                            'fee' => $combinedResult['fee'],
                            'net' => $amount - $combinedResult['fee'],
                            'details' => $combinedResult['details'],
                        ];
                    }
                }
            }

            // === 3. Trier et limiter ===
            usort($monoOptions, fn($a, $b) => $a['fee'] <=> $b['fee']);
            usort($multiOptions, fn($a, $b) => $a['fee'] <=> $b['fee']);

            $multiOptions = array_slice($multiOptions, 0, 3);

            $allOptions = array_merge($monoOptions, $multiOptions);

            $uniqueOptions = [];
            $seenLabels = [];
            foreach ($allOptions as $opt) {
                if (!in_array($opt['label'], $seenLabels)) {
                    $uniqueOptions[] = $opt;
                    $seenLabels[] = $opt['label'];
                }
            }
            $allOptions = $uniqueOptions;

            $selected = array_slice($allOptions, 0, 12);

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
     * Fractionnement optimal par la méthode des bornes supérieures successives.
     */
    private function optimalSplit($fees, float $amount, string $networkName): ?array
    {
        $fees = $fees->sortBy('max_amount')->values();
        if ($fees->isEmpty()) return null;

        $bounds = $fees->pluck('max_amount')->map(fn($v) => (float) $v)->unique()->sort()->values()->toArray();

        $bestCombination = null;
        $bestFee = PHP_FLOAT_MAX;

        // 1. Décomposition en 2 parts
        $bestBound = null;
        foreach ($bounds as $bound) {
            if ($bound < $amount && ($bestBound === null || $bound > $bestBound)) {
                $bestBound = $bound;
            }
        }
        if ($bestBound !== null) {
            $part1 = $bestBound;
            $part2 = $amount - $part1;
            if ($part2 >= 100) {
                $fee1 = $this->getFeeForAmount($fees, $part1);
                $fee2 = $this->getFeeForAmount($fees, $part2);
                $totalFee = $fee1 + $fee2;
                $bestFee = $totalFee;
                $bestCombination = [
                    'fee' => $totalFee,
                    'details' => [
                        ['network' => $networkName, 'amount' => $part1, 'fee' => $fee1],
                        ['network' => $networkName, 'amount' => $part2, 'fee' => $fee2],
                    ],
                ];
            }
        }

        // 2. Décomposition en 3 parts
        for ($i = 0; $i < count($bounds); $i++) {
            for ($j = $i + 1; $j < count($bounds); $j++) {
                $part1 = $bounds[$i];
                $part2 = $bounds[$j];
                $part3 = $amount - $part1 - $part2;
                if ($part3 < 100 || $part1 <= 0 || $part2 <= 0) continue;
                $fee1 = $this->getFeeForAmount($fees, $part1);
                $fee2 = $this->getFeeForAmount($fees, $part2);
                $fee3 = $this->getFeeForAmount($fees, $part3);
                $totalFee = $fee1 + $fee2 + $fee3;
                if ($totalFee < $bestFee) {
                    $bestFee = $totalFee;
                    $bestCombination = [
                        'fee' => $totalFee,
                        'details' => [
                            ['network' => $networkName, 'amount' => $part1, 'fee' => $fee1],
                            ['network' => $networkName, 'amount' => $part2, 'fee' => $fee2],
                            ['network' => $networkName, 'amount' => $part3, 'fee' => $fee3],
                        ],
                    ];
                }
            }
        }

        return $bestCombination;
    }

    /**
     * Vérifie si deux méthodes sont compatibles pour une combinaison
     */
    private function areMethodsCompatible($methodA, $methodB): bool
    {
        // Si les deux méthodes sont dans la même catégorie, on autorise
        if ($methodA->category === $methodB->category) {
            return true;
        }

        // Catégories différentes → interdites
        return false;
    }

    /**
     * Combinaison optimale entre deux réseaux (1 part sur A, 1 part sur B).
     */
    private function optimalCombinedSplit($feesA, $feesB, float $amount, string $nameA, string $nameB, $methodA, $methodB): ?array
    {
        // 🔥 FILTRE : Vérifier si les méthodes sont compatibles
        if (!$this->areMethodsCompatible($methodA, $methodB)) {
            return null;
        }

        $feesA = $feesA->sortBy('max_amount')->values();
        $feesB = $feesB->sortBy('max_amount')->values();

        if ($feesA->isEmpty() || $feesB->isEmpty()) return null;

        $bestResult = null;
        $bestFee = PHP_FLOAT_MAX;

        // Tester les bornes supérieures de A
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

        // Tester les bornes supérieures de B
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

    /**
     * Calcule le frais pour un montant donné sur un ensemble de paliers.
     */
    private function getFeeForAmount($fees, float $amount): float
    {
        foreach ($fees as $tier) {
            if ($amount >= $tier->min_amount && $amount <= $tier->max_amount) {
                return (float) $tier->fee_amount;
            }
        }
        $last = $fees->last();
        return $last ? (float) $last->fee_amount : 0.0;
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