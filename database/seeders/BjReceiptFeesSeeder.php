<?php

namespace Database\Seeders;

use App\Models\Method;
use App\Models\ReceiptFee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class BjReceiptFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Prépare les frais de retrait pour les opérateurs du Bénin
     * (Celtiis Cash, MTN MoMo, Moov Money)
     */
    public function run(): void
    {
        $this->command->info('🔄 Début de l\'importation des frais de retrait pour le Bénin...');

        // Récupérer les méthodes
        $methods = Method::whereIn('code', ['celtiis_bj', 'mtn_bj', 'moov_bj'])->get();

        if ($methods->isEmpty()) {
            $this->command->error('❌ Aucune méthode trouvée pour les codes : celtiis_bj, mtn_bj, moov_bj');
            return;
        }

        // Grilles tarifaires par méthode
        $grids = [
            'celtiis_bj' => [
                'method_code' => 'celtiis_bj',
                'tiers' => [
                    ['min' => 100, 'max' => 500, 'fee' => 50],
                    ['min' => 501, 'max' => 5000, 'fee' => 120],
                    ['min' => 5001, 'max' => 10000, 'fee' => 200],
                    ['min' => 10001, 'max' => 20000, 'fee' => 300],
                    ['min' => 20001, 'max' => 50000, 'fee' => 600],
                    ['min' => 50001, 'max' => 75000, 'fee' => 900],
                    ['min' => 75001, 'max' => 100000, 'fee' => 1000],
                    ['min' => 100001, 'max' => 200000, 'fee' => 2000],
                    ['min' => 200001, 'max' => 300000, 'fee' => 3000],
                    ['min' => 300001, 'max' => 500000, 'fee' => 3500],
                    ['min' => 500001, 'max' => 750000, 'fee' => 5000],
                    ['min' => 750001, 'max' => 1000000, 'fee' => 5800],
                    ['min' => 1000001, 'max' => 1500000, 'fee' => 7800],
                    ['min' => 1500001, 'max' => 2000000, 'fee' => 9800],
                ],
            ],
            'mtn_bj' => [
                'method_code' => 'mtn_bj',
                'tiers' => [
                    ['min' => 1, 'max' => 500, 'fee' => 50],
                    ['min' => 501, 'max' => 5000, 'fee' => 125],
                    ['min' => 5001, 'max' => 10000, 'fee' => 225],
                    ['min' => 10001, 'max' => 20000, 'fee' => 375],
                    ['min' => 20001, 'max' => 50000, 'fee' => 700],
                    ['min' => 50001, 'max' => 100000, 'fee' => 1000],
                    ['min' => 100001, 'max' => 200000, 'fee' => 2000],
                    ['min' => 200001, 'max' => 300000, 'fee' => 3000],
                    ['min' => 300001, 'max' => 500000, 'fee' => 3500],
                    ['min' => 500001, 'max' => 750000, 'fee' => 5000],
                    ['min' => 750001, 'max' => 1000000, 'fee' => 6000],
                    ['min' => 1000001, 'max' => 1500000, 'fee' => 8000],
                    ['min' => 1500001, 'max' => 2000000, 'fee' => 9900],
                ],
            ],
            'moov_bj' => [
                'method_code' => 'moov_bj',
                'tiers' => [
                    ['min' => 100, 'max' => 500, 'fee' => 50],
                    ['min' => 501, 'max' => 5000, 'fee' => 125],
                    ['min' => 5001, 'max' => 10000, 'fee' => 225],
                    ['min' => 10001, 'max' => 20000, 'fee' => 375],
                    ['min' => 20001, 'max' => 50000, 'fee' => 700],
                    ['min' => 50001, 'max' => 75000, 'fee' => 1000],
                    ['min' => 75001, 'max' => 100000, 'fee' => 1000],
                    ['min' => 100001, 'max' => 200000, 'fee' => 2000],
                    ['min' => 200001, 'max' => 300000, 'fee' => 3000],
                    ['min' => 300001, 'max' => 400000, 'fee' => 3500],
                    ['min' => 400001, 'max' => 500000, 'fee' => 3500],
                    ['min' => 500001, 'max' => 750000, 'fee' => 5000],
                    ['min' => 750001, 'max' => 1000000, 'fee' => 6000],
                    ['min' => 1000001, 'max' => 1500000, 'fee' => 8000],
                    ['min' => 1500001, 'max' => 2000000, 'fee' => 9900],
                ],
            ],
        ];

        $totalInserted = 0;

        foreach ($grids as $code => $grid) {
            $method = $methods->firstWhere('code', $code);

            if (!$method) {
                $this->command->warn("⚠️ Méthode {$code} non trouvée, ignorée.");
                continue;
            }

            // Supprimer les anciens frais de retrait pour cette méthode au Bénin
            $deleted = ReceiptFee::where('method_id', $method->id)
                ->where('country_code', 'BJ')
                ->delete();

            if ($deleted > 0) {
                $this->command->info("🗑️ {$deleted} ancien(s) palier(s) supprimé(s) pour {$method->name}");
            }

            // Insérer les nouveaux paliers
            foreach ($grid['tiers'] as $tier) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => 'BJ',
                    'min_amount' => $tier['min'],
                    'max_amount' => $tier['max'],
                    'fee_amount' => $tier['fee'],
                    'fee_type' => 'fixed',
                    'method_name' => $method->name,
                ]);
                $totalInserted++;
            }

            $this->command->info("✅ " . count($grid['tiers']) . " paliers importés pour {$method->name}");
        }

        $this->command->info("🎉 Importation terminée : {$totalInserted} paliers de retrait insérés pour le Bénin !");
    }
}