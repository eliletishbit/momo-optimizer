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
        Log::info('🔍 FeeOptimizerAdapter – début', [
            'amount' => $amount,
            'country' => $countryCode,
            'method_count' => count($userMethodIds),
            'type' => $type,
        ]);

        $cacheKey = 'optimization_' . md5($amount . '_' . implode('_', $userMethodIds) . '_' . $countryCode . '_' . $type);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($amount, $userMethodIds, $countryCode) {
            return $this->classicFeeOptimizer->optimizeWithdrawal($amount, $userMethodIds, $countryCode);
        });
    }
}