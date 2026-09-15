<?php

namespace App\Services;

use App\Models\Method;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicFeeOptimizer
{
    /**
     * Version publique simplifiée de l'optimisation
     * ✅ Limité aux méthodes publiques
     * ✅ Résultats simplifiés
     * ✅ Pas d'historique
     * ✅ Cache plus court
     */
    public function optimizeWithdrawal(float $amount, array $methodIds, string $countryCode = 'BJ'): array
    {
        $startTime = microtime(true);
        
        Log::info('📊 PublicFeeOptimizer appelé', [
            'amount' => $amount,
            'country' => $countryCode,
            'method_count' => count($methodIds),
        ]);

        $amount = (float) $amount;

        if ($amount < 100) {
            return $this->buildErrorResponse('Montant minimum : 100 FCFA.');
        }

        $methodIds = array_values(array_unique(array_filter(array_map('strval', $methodIds))));
        if (empty($methodIds)) {
            return $this->buildErrorResponse('Aucune méthode de paiement disponible.');
        }

        // ✅ Cache plus court pour le public (1h au lieu de 24h)
        $cacheKey = 'public_fee_optimizer:withdrawal:' . md5(
            implode('-', $methodIds) . 
            ':' . (int) round($amount) . 
            ':' . $countryCode .
            ':' . config('app.fees_version', '1.0')
        );

        $result = Cache::remember($cacheKey, now()->addHours(1), function () use ($amount, $methodIds, $countryCode) {
            
            $methods = $this->getPublicMethodsWithFees($methodIds, $countryCode);

            if ($methods->isEmpty()) {
                return $this->buildFallbackResponse($amount);
            }

            // ✅ Générer les options (simplifiées pour le public)
            $options = [];

            foreach ($methods as $method) {
                if ($method->receiptFees->isEmpty()) {
                    continue;
                }

                // Option "réseau seul"
                $singleFee = $this->getFeeForAmount($method->receiptFees, $amount);
                $options[] = [
                    'type' => 'single',
                    'label' => $method->name . ' seul',
                    'fee' => $singleFee,
                    'net' => $amount - $singleFee,
                    'details' => [['network' => $method->name, 'amount' => $amount, 'fee' => $singleFee]],
                ];
            }

            // ✅ Trier et limiter à 5 options (moins que le privé)
            usort($options, fn($a, $b) => $a['fee'] <=> $b['fee']);
            $selected = array_slice($options, 0, 5);

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
                'is_public' => true, // ✅ Flag pour identifier que c'est une version publique
            ];
        });

        $executionTime = round(microtime(true) - $startTime, 3);
        Log::info('⏱️ PublicFeeOptimizer exécuté en ' . $executionTime . ' secondes', [
            'amount' => $amount,
            'country' => $countryCode,
        ]);

        return $result;
    }

    /**
     * ✅ Récupère les méthodes publiques avec leurs frais
     * Version simplifiée sans cache interne (le cache est déjà géré)
     */
    // app/Services/PublicFeeOptimizer.php

private function getPublicMethodsWithFees(array $methodIds, string $countryCode): Collection
{
    return Method::whereIn('id', $methodIds)
        ->whereRaw('is_active = true') // ✅ PostgreSQL
        ->where(function ($query) use ($countryCode) {
            $query->where('country_code', $countryCode)
                  ->orWhereNull('country_code');
        })
        ->with(['receiptFees' => function ($query) use ($countryCode) {
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
     * ✅ Calcule le frais pour un montant donné
     */
    private function getFeeForAmount($fees, float $amount): float
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
     * ✅ Fallback en cas d'erreur
     */
    private function buildFallbackResponse(float $amount): array
    {
        Log::warning('⚠️ PublicFeeOptimizer - Fallback utilisé');
        
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

    /**
     * ✅ Calcule les frais standards pour le fallback
     */
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

    /**
     * ✅ Construit une réponse d'erreur
     */
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