<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\FeeOptimizer;
use App\Services\IAFeeOptimizer;

class FeeOptimizerAdapter
{
    protected $iaFeeOptimizer;
    protected $classicFeeOptimizer;

    public function __construct(
        IAFeeOptimizer $iaFeeOptimizer,
        FeeOptimizer $classicFeeOptimizer
    ) {
        $this->iaFeeOptimizer = $iaFeeOptimizer;
        $this->classicFeeOptimizer = $classicFeeOptimizer;
    }

    public function optimizeWithdrawal(float $amount, array $userMethodIds, string $countryCode = 'BJ', string $type = 'withdrawal'): array
    {
        // ✅ LOG DE DEBUG POUR VOIR CE QUI EST PASSE
        Log::info('🔍 FeeOptimizerAdapter – pays reçu', [
            'countryCode' => $countryCode,
            'amount' => $amount,
            'method_count' => count($userMethodIds),
            'type' => $type,
        ]);

        $cacheKey = 'optimization_' . md5($amount . '_' . implode('_', $userMethodIds) . '_' . $countryCode . '_' . $type);

        Log::info('🔍 FeeOptimizerAdapter – début', [
            'amount' => $amount,
            'country' => $countryCode,
            'type' => $type,
            'cache_key' => $cacheKey,
        ]);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($amount, $userMethodIds, $countryCode, $type) {
            Log::info('🔍 Cache MISS – calcul en cours');

            $iaAvailable = Cache::remember('ia_available', now()->addMinutes(5), function () {
                return $this->iaFeeOptimizer->isAvailable();
            });

            Log::info('🔍 IA disponible ? ' . ($iaAvailable ? '✅ OUI' : '❌ NON'));

            if ($iaAvailable) {
                $result = $this->iaFeeOptimizer->optimize($amount, $userMethodIds, $countryCode, $type);
                if (!isset($result['error'])) {
                    Log::info('✅ IA OK – retour avec is_ai = true');
                    return $result;
                }
                Log::warning('⚠️ IA en erreur : ' . ($result['error'] ?? 'Unknown'));
            }

            Log::info('🔄 Fallback vers le classique FeeOptimizer avec pays: ' . $countryCode);
            
            // ✅ Passer le pays correctement au classique
            $result = $this->classicFeeOptimizer->optimizeWithdrawal($amount, $userMethodIds, $countryCode);
            
            Log::info('✅ Fallback terminé pour le pays: ' . $countryCode);
            
            return $result;
        });
    }
}