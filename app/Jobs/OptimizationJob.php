<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\OptimizationHistory;
use App\Models\Method;
use App\Services\FeeOptimizerAdapter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        public string $userId,
        public float $amount,
        public string $country,
        public string $type,
        public array $methodIds,
        public string $cacheKey
    ) {}

    public function handle(FeeOptimizerAdapter $optimizer): void
    {
        try {
            Log::info('🔄 [JOB] Début du calcul', [
                'user_id' => $this->userId,
                'amount' => $this->amount,
                'cache_key' => $this->cacheKey,
            ]);

            // ✅ Exécuter le calcul
            $result = $optimizer->optimizeWithdrawal(
                $this->amount,
                $this->methodIds,
                $this->country,
                $this->type,
            );

            // ✅ Sauvegarder le résultat dans le cache
            Cache::put($this->cacheKey, $result, now()->addMinutes(30));

            // ✅ Sauvegarder l'historique
            $this->saveOptimization($result);

            Log::info('✅ [JOB] Calcul terminé', [
                'user_id' => $this->userId,
                'has_error' => isset($result['error']),
                'has_best' => isset($result['best']),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [JOB] Erreur: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($this->cacheKey, ['error' => 'Une erreur est survenue lors du calcul.'], now()->addMinutes(5));
        }
    }

    private function saveOptimization(array $result): void
    {
        try {
            if (!isset($result['best']) || isset($result['error'])) {
                return;
            }

            $user = User::find($this->userId);
            if (!$user) {
                return;
            }

            $methodId = null;
            if (isset($result['best']['details'][0]['method_id'])) {
                $methodId = $result['best']['details'][0]['method_id'];
            } elseif (isset($result['best']['details'][0]['network'])) {
                $networkName = $result['best']['details'][0]['network'];
                $methodId = Cache::remember("method_id_{$networkName}", 3600, function () use ($networkName) {
                    return Method::where('name', $networkName)->value('id');
                });
            }

            OptimizationHistory::create([
                'user_id' => $this->userId,
                'type' => $this->type === 'withdrawal' ? 'receipt' : 'send',
                'amount' => $this->amount,
                'selected_method_id' => $methodId ?? $this->methodIds[0] ?? null,
                'total_fee' => $result['best']['fee'] ?? 0,
                'savings' => $result['savings'] ?? 0,
                'alternatives' => $result['alternatives'] ?? [],
            ]);

            // ✅ Vider le cache des stats
            foreach ([
                "user_stats_{$this->userId}",
                "user_optimizations_count_{$this->userId}",
                "user_total_savings_{$this->userId}",
                "dashboard_stats_{$this->userId}",
                "user_recent_optimizations_{$this->userId}",
            ] as $key) {
                if (Cache::has($key)) {
                    Cache::forget($key);
                }
            }

            Log::info('✅ [JOB] Historique sauvegardé', ['user_id' => $this->userId]);

        } catch (\Exception $e) {
            Log::error('❌ [JOB] Erreur sauvegarde: ' . $e->getMessage());
        }
    }
}