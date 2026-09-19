<?php

namespace App\Services;

use App\Models\Method;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicFeeOptimizer
{
    /**
     * Optimise les frais de retrait ou d'envoi pour le calculateur public
     * Supporte :
     * - Niveau 1 : Réseau seul (1 tranche)
     * - Niveau 2 : Fractionnement optimal en 2 tranches (mono-réseau & multi-réseaux)
     * - Niveau 3 : Fractionnement optimal en 3 tranches (mono-réseau & multi-réseaux)
     */
    public function optimizeWithdrawal(float $amount, array $methodIds, string $countryCode = 'BJ', string $type = 'withdrawal'): array
    {
        $amount = (float) $amount;

        if ($amount < 100) {
            return $this->buildErrorResponse('Montant minimum : 100 FCFA.');
        }

        $methodIds = array_values(array_unique(array_filter(array_map('strval', $methodIds))));
        if (empty($methodIds)) {
            return $this->buildErrorResponse('Aucune méthode de paiement disponible.');
        }

        $cacheKey = 'public_fee_optimizer:' . $type . ':' . md5(
            implode('-', $methodIds) . 
            ':' . (int) round($amount) . 
            ':' . $countryCode .
            ':' . config('app.fees_version', '2.0')
        );

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($amount, $methodIds, $countryCode, $type) {
            $methods = $this->getPublicMethodsWithFees($methodIds, $countryCode, $type);

            if ($methods->isEmpty()) {
                return $this->buildFallbackResponse($amount);
            }

            $allOptions = [];

            // 1. NIVEAU 1 : Réseau seul (1 tranche directe)
            foreach ($methods as $method) {
                $fees = $this->extractFees($method, $type);
                if ($fees->isEmpty()) continue;

                $singleFee = $this->getFeeForAmount($fees, $amount);
                $allOptions[] = [
                    'type' => 'single',
                    'label' => $method->name . ' (1 opération directe)',
                    'fee' => $singleFee,
                    'net' => $amount - $singleFee,
                    'amount_to_receive' => $amount,
                    'details' => [
                        ['network' => $method->name, 'amount' => $amount, 'fee' => $singleFee]
                    ],
                ];
            }

            // 2. NIVEAU 2 : Fractionnement en 2 tranches (Mono-réseau)
            foreach ($methods as $method) {
                $fees = $this->extractFees($method, $type);
                if ($fees->isEmpty()) continue;

                $split2 = $this->optimalTwoSplit($fees, $amount, $method->name);
                if ($split2) {
                    $allOptions[] = [
                        'type' => 'optimal_split',
                        'label' => $method->name . ' en 2 tranches (optimisé)',
                        'fee' => $split2['fee'],
                        'net' => $amount - $split2['fee'],
                        'amount_to_receive' => $amount,
                        'details' => $split2['details'],
                    ];
                }
            }

            // 3. NIVEAU 2 : Combinaison de 2 réseaux (Bi-réseaux en 2 tranches)
            $methodList = $methods->values();
            $count = $methodList->count();
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $mA = $methodList[$i];
                    $mB = $methodList[$j];
                    $feesA = $this->extractFees($mA, $type);
                    $feesB = $this->extractFees($mB, $type);
                    if ($feesA->isEmpty() || $feesB->isEmpty()) continue;

                    $combined2 = $this->optimalCombinedTwoSplit($feesA, $feesB, $amount, $mA->name, $mB->name);
                    if ($combined2) {
                        $allOptions[] = [
                            'type' => 'combined',
                            'label' => $mA->name . ' + ' . $mB->name . ' (2 tranches)',
                            'fee' => $combined2['fee'],
                            'net' => $amount - $combined2['fee'],
                            'amount_to_receive' => $amount,
                            'details' => $combined2['details'],
                        ];
                    }
                }
            }

            // 4. NIVEAU 3 : Fractionnement en 3 tranches (Mono-réseau si réduction de frais)
            foreach ($methods as $method) {
                $fees = $this->extractFees($method, $type);
                if ($fees->isEmpty()) continue;

                $split3 = $this->optimalThreeSplit($fees, $amount, $method->name);
                if ($split3) {
                    $allOptions[] = [
                        'type' => 'three_tier',
                        'label' => $method->name . ' en 3 tranches (ultra-optimisé)',
                        'fee' => $split3['fee'],
                        'net' => $amount - $split3['fee'],
                        'amount_to_receive' => $amount,
                        'details' => $split3['details'],
                    ];
                }
            }

            // 5. NIVEAU 3 : Combinaison en 3 tranches (Multi-réseaux si avantageux)
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $mA = $methodList[$i];
                    $mB = $methodList[$j];
                    $feesA = $this->extractFees($mA, $type);
                    $feesB = $this->extractFees($mB, $type);
                    if ($feesA->isEmpty() || $feesB->isEmpty()) continue;

                    $combined3 = $this->optimalCombinedThreeSplit($feesA, $feesB, $amount, $mA->name, $mB->name);
                    if ($combined3) {
                        $allOptions[] = [
                            'type' => 'combined_three',
                            'label' => $mA->name . ' + ' . $mB->name . ' (3 tranches)',
                            'fee' => $combined3['fee'],
                            'net' => $amount - $combined3['fee'],
                            'amount_to_receive' => $amount,
                            'details' => $combined3['details'],
                        ];
                    }
                }
            }

            // Trier toutes les options par frais croissants
            usort($allOptions, fn($a, $b) => $a['fee'] <=> $b['fee']);

            // Éliminer les doublons de label et ne retenir que les meilleures
            $selected = [];
            $usedLabels = [];
            foreach ($allOptions as $opt) {
                if (count($selected) >= 12) break;
                if (!in_array($opt['label'], $usedLabels)) {
                    $selected[] = $opt;
                    $usedLabels[] = $opt['label'];
                }
            }

            if (empty($selected)) {
                return $this->buildFallbackResponse($amount);
            }

            $best = $selected[0];
            $worst = $selected[count($selected) - 1];
            $savings = max(0, $worst['fee'] - $best['fee']);

            return [
                'amount' => (int) round($amount),
                'best' => $best,
                'alternatives' => array_slice($selected, 1),
                'savings' => (int) round($savings),
                'from_cache' => false,
                'is_public' => true,
            ];
        });
    }

    /**
     * Extrait les paliers tarifaires appropriés selon le type (retrait ou envoi)
     */
    private function extractFees(Method $method, string $type): Collection
    {
        if ($type === 'sending' && $method->relationLoaded('sendingFees') && $method->sendingFees->isNotEmpty()) {
            return $method->sendingFees->sortBy('max_amount')->values();
        }
        if ($method->relationLoaded('receiptFees') && $method->receiptFees->isNotEmpty()) {
            return $method->receiptFees->sortBy('max_amount')->values();
        }
        return collect();
    }

    /**
     * Fractionnement optimal en 2 tranches sur un seul réseau
     */
    private function optimalTwoSplit(Collection $fees, float $amount, string $networkName): ?array
    {
        if ($fees->isEmpty() || $amount < 200) return null;

        $singleFee = $this->getFeeForAmount($fees, $amount);
        $bestFee = $singleFee;
        $bestDetails = null;

        // 1. Tester chaque limite de palier comme première tranche
        foreach ($fees as $tier) {
            $p1 = (float) $tier->max_amount;
            if ($p1 > 0 && $p1 < $amount && ($amount - $p1) >= 100) {
                $p2 = $amount - $p1;
                $f1 = $this->getFeeForAmount($fees, $p1);
                $f2 = $this->getFeeForAmount($fees, $p2);
                $tot = $f1 + $f2;
                if ($tot < $bestFee) {
                    $bestFee = $tot;
                    $bestDetails = [
                        ['network' => $networkName, 'amount' => $p1, 'fee' => $f1],
                        ['network' => $networkName, 'amount' => $p2, 'fee' => $f2],
                    ];
                }
            }
        }

        // 2. Tester le découpage à parts égales (50% / 50%)
        $half1 = round($amount / 2);
        $half2 = $amount - $half1;
        if ($half1 >= 100 && $half2 >= 100) {
            $f1 = $this->getFeeForAmount($fees, $half1);
            $f2 = $this->getFeeForAmount($fees, $half2);
            $tot = $f1 + $f2;
            if ($tot < $bestFee) {
                $bestFee = $tot;
                $bestDetails = [
                    ['network' => $networkName, 'amount' => $half1, 'fee' => $f1],
                    ['network' => $networkName, 'amount' => $half2, 'fee' => $f2],
                ];
            }
        }

        if ($bestDetails && $bestFee < $singleFee) {
            return ['fee' => $bestFee, 'details' => $bestDetails];
        }

        return null;
    }

    /**
     * Fractionnement optimal en 2 tranches combinant 2 réseaux (A + B)
     */
    private function optimalCombinedTwoSplit(Collection $feesA, Collection $feesB, float $amount, string $nameA, string $nameB): ?array
    {
        if ($amount < 200) return null;

        $bestFee = PHP_FLOAT_MAX;
        $bestDetails = null;

        // Tester les seuils de A
        foreach ($feesA as $tierA) {
            $pA = (float) $tierA->max_amount;
            if ($pA > 0 && $pA < $amount && ($amount - $pA) >= 100) {
                $pB = $amount - $pA;
                $fA = $this->getFeeForAmount($feesA, $pA);
                $fB = $this->getFeeForAmount($feesB, $pB);
                $tot = $fA + $fB;
                if ($tot < $bestFee) {
                    $bestFee = $tot;
                    $bestDetails = [
                        ['network' => $nameA, 'amount' => $pA, 'fee' => $fA],
                        ['network' => $nameB, 'amount' => $pB, 'fee' => $fB],
                    ];
                }
            }
        }

        // Tester les seuils de B
        foreach ($feesB as $tierB) {
            $pB = (float) $tierB->max_amount;
            if ($pB > 0 && $pB < $amount && ($amount - $pB) >= 100) {
                $pA = $amount - $pB;
                $fB = $this->getFeeForAmount($feesB, $pB);
                $fA = $this->getFeeForAmount($feesA, $pA);
                $tot = $fA + $fB;
                if ($tot < $bestFee) {
                    $bestFee = $tot;
                    $bestDetails = [
                        ['network' => $nameA, 'amount' => $pA, 'fee' => $fA],
                        ['network' => $nameB, 'amount' => $pB, 'fee' => $fB],
                    ];
                }
            }
        }

        // Tester le partage équitable 50/50
        $halfA = round($amount / 2);
        $halfB = $amount - $halfA;
        if ($halfA >= 100 && $halfB >= 100) {
            $fA = $this->getFeeForAmount($feesA, $halfA);
            $fB = $this->getFeeForAmount($feesB, $halfB);
            $tot = $fA + $fB;
            if ($tot < $bestFee) {
                $bestFee = $tot;
                $bestDetails = [
                    ['network' => $nameA, 'amount' => $halfA, 'fee' => $fA],
                    ['network' => $nameB, 'amount' => $halfB, 'fee' => $fB],
                ];
            }
        }

        if ($bestDetails) {
            return ['fee' => $bestFee, 'details' => $bestDetails];
        }

        return null;
    }

    /**
     * Fractionnement optimal en 3 tranches sur un seul réseau (Niveau 3)
     */
    private function optimalThreeSplit(Collection $fees, float $amount, string $networkName): ?array
    {
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
                    ['network' => $networkName, 'amount' => $third, 'fee' => $f1],
                    ['network' => $networkName, 'amount' => $third, 'fee' => $f2],
                    ['network' => $networkName, 'amount' => $rem, 'fee' => $f3],
                ];
            }
        }

        // 2. Découpage basé sur les plafonds de paliers (2 paliers + solde)
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
                                ['network' => $networkName, 'amount' => $t1, 'fee' => $f1],
                                ['network' => $networkName, 'amount' => $t2, 'fee' => $f2],
                                ['network' => $networkName, 'amount' => $t3, 'fee' => $f3],
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
     * Fractionnement optimal en 3 tranches multi-réseaux (ex: 2 tranches sur A, 1 sur B)
     */
    private function optimalCombinedThreeSplit(Collection $feesA, Collection $feesB, float $amount, string $nameA, string $nameB): ?array
    {
        if ($amount < 300) return null;

        $bestFee = PHP_FLOAT_MAX;
        $bestDetails = null;

        // Tester 2 tiers sur A, 1 tiers sur B
        $third = round($amount / 3);
        $rem = $amount - (2 * $third);
        if ($third >= 100 && $rem >= 100) {
            $fA1 = $this->getFeeForAmount($feesA, $third);
            $fA2 = $this->getFeeForAmount($feesA, $third);
            $fB = $this->getFeeForAmount($feesB, $rem);
            $tot = $fA1 + $fA2 + $fB;
            if ($tot < $bestFee) {
                $bestFee = $tot;
                $bestDetails = [
                    ['network' => $nameA, 'amount' => $third, 'fee' => $fA1],
                    ['network' => $nameA, 'amount' => $third, 'fee' => $fA2],
                    ['network' => $nameB, 'amount' => $rem, 'fee' => $fB],
                ];
            }
        }

        // Et inversement : 2 tiers sur B, 1 sur A
        if ($third >= 100 && $rem >= 100) {
            $fB1 = $this->getFeeForAmount($feesB, $third);
            $fB2 = $this->getFeeForAmount($feesB, $third);
            $fA = $this->getFeeForAmount($feesA, $rem);
            $tot = $fB1 + $fB2 + $fA;
            if ($tot < $bestFee) {
                $bestFee = $tot;
                $bestDetails = [
                    ['network' => $nameB, 'amount' => $third, 'fee' => $fB1],
                    ['network' => $nameB, 'amount' => $third, 'fee' => $fB2],
                    ['network' => $nameA, 'amount' => $rem, 'fee' => $fA],
                ];
            }
        }

        if ($bestDetails) {
            return ['fee' => $bestFee, 'details' => $bestDetails];
        }

        return null;
    }

    /**
     * Récupère les méthodes publiques avec leurs paliers (retrait ou envoi)
     */
    private function getPublicMethodsWithFees(array $methodIds, string $countryCode, string $type = 'withdrawal'): Collection
    {
        $relation = $type === 'sending' ? 'sendingFees' : 'receiptFees';

        return Method::whereIn('id', $methodIds)
            ->whereRaw('is_active = true')
            ->where(function ($query) use ($countryCode) {
                $query->where('country_code', $countryCode)
                      ->orWhereNull('country_code');
            })
            ->with([$relation => function ($query) use ($countryCode) {
                $query->where(function ($q) use ($countryCode) {
                    $q->where('country_code', $countryCode)
                      ->orWhereNull('country_code');
                })
                ->orderBy('max_amount', 'asc')
                ->select('method_id', 'min_amount', 'max_amount', 'fee_amount', 'fee_type');
            }])
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'category', 'country_code')
            ->get();
    }

    /**
     * Calcule les frais pour un montant donné sur un ensemble de paliers
     */
    private function getFeeForAmount(Collection $fees, float $amount): float
    {
        foreach ($fees as $tier) {
            if ($amount >= (float) $tier->min_amount && $amount <= (float) $tier->max_amount) {
                return (float) $tier->fee_amount;
            }
        }
        $last = $fees->last();
        return $last ? (float) $last->fee_amount : 0.0;
    }

    /**
     * Fallback standard en cas d'absence de méthode configurée
     */
    private function buildFallbackResponse(float $amount): array
    {
        $standardFee = $this->calculateStandardFee($amount);
        $option = [
            'type' => 'single',
            'label' => 'Méthode standard',
            'fee' => $standardFee,
            'net' => $amount - $standardFee,
            'details' => [['network' => 'Standard', 'amount' => $amount, 'fee' => $standardFee]],
        ];

        return [
            'amount' => (int) round($amount),
            'best' => $option,
            'alternatives' => [],
            'savings' => 0,
            'from_cache' => false,
            'fallback' => true,
            'is_public' => true,
        ];
    }

    private function calculateStandardFee(float $amount): float
    {
        if ($amount <= 500) return 50;
        if ($amount <= 5000) return 125;
        if ($amount <= 10000) return 225;
        if ($amount <= 20000) return 375;
        if ($amount <= 50000) return 700;
        if ($amount <= 100000) return 1000;
        if ($amount <= 200000) return 2000;
        if ($amount <= 300000) return 3000;
        if ($amount <= 500000) return 3500;
        if ($amount <= 750000) return 5000;
        if ($amount <= 1000000) return 6000;
        if ($amount <= 1500000) return 8000;
        return 9900;
    }

    private function buildErrorResponse(string $message): array
    {
        return [
            'error' => $message,
            'amount' => 0,
            'best' => null,
            'alternatives' => [],
            'savings' => 0,
            'from_cache' => false,
            'is_public' => true,
        ];
    }
}