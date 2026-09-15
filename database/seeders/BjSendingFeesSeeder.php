<?php

namespace Database\Seeders;

use App\Models\Method;
use App\Models\SendingFee;
use Illuminate\Database\Seeder;

class BjSendingFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Prépare les frais d'envoi pour les opérateurs du Bénin
     * (Celtiis Cash, MTN MoMo, Moov Money)
     */
    public function run(): void
    {
        $this->command->info('🔄 Début de l\'importation des frais d\'envoi pour le Bénin...');

        // Récupérer les méthodes
        $methods = Method::whereIn('code', ['celtiis_bj', 'mtn_bj', 'moov_bj'])->get();

        if ($methods->isEmpty()) {
            $this->command->error('❌ Aucune méthode trouvée pour les codes : celtiis_bj, mtn_bj, moov_bj');
            return;
        }

        // Grilles tarifaires d'envoi par méthode
        $grids = [
            'celtiis_bj' => [
                'method_code' => 'celtiis_bj',
                'tiers' => [
                    // Transfert entre abonnés (gratuit)
                    ['min' => 0, 'max' => 9999999, 'fee' => 0, 'type' => 'fixed'],
                    // Dépôts (gratuit)
                    ['min' => 0, 'max' => 9999999, 'fee' => 0, 'type' => 'fixed'],
                    // Paiement de facture celtiis (gratuit)
                    ['min' => 0, 'max' => 9999999, 'fee' => 0, 'type' => 'fixed'],
                    // Paiement Marchand (1% max 1000 F) - on utilise 'percentage' avec 1%
                    ['min' => 0, 'max' => 9999999, 'fee' => 1, 'type' => 'percentage'],
                    // Paiement Canal+ (150 F)
                    ['min' => 0, 'max' => 9999999, 'fee' => 150, 'type' => 'fixed'],
                    // Paiement autres factures (< 1000 F gratuit)
                    ['min' => 0, 'max' => 1000, 'fee' => 0, 'type' => 'fixed'],
                    // Paiement autres factures (> 1000 F)
                    ['min' => 1001, 'max' => 9999999, 'fee' => 50, 'type' => 'fixed'],
                ],
            ],
            'mtn_bj' => [
                'method_code' => 'mtn_bj',
                'tiers' => [
                    // Transfert entre abonnés (50 F)
                    ['min' => 0, 'max' => 9999999, 'fee' => 50, 'type' => 'fixed'],
                    // Transfert (3 premières < 1000 F/mois gratuit)
                    ['min' => 0, 'max' => 1000, 'fee' => 0, 'type' => 'fixed'],
                ],
            ],
            'moov_bj' => [
                'method_code' => 'moov_bj',
                'tiers' => [
                    // Transfert national via Appli Moov (50 F)
                    ['min' => 1, 'max' => 2000000, 'fee' => 50, 'type' => 'fixed'],
                    
                    // Transfert national via USSD (< 1000 F gratuit)
                    ['min' => 1, 'max' => 1000, 'fee' => 0, 'type' => 'fixed'],
                    // Transfert national via USSD (> 1000 F)
                    ['min' => 1001, 'max' => 2000000, 'fee' => 100, 'type' => 'fixed'],
                    
                    // Transfert vers autre réseau
                    ['min' => 1, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                    ['min' => 501, 'max' => 5000, 'fee' => 100, 'type' => 'fixed'],
                    ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                    ['min' => 10001, 'max' => 20000, 'fee' => 350, 'type' => 'fixed'],
                    ['min' => 20001, 'max' => 50000, 'fee' => 700, 'type' => 'fixed'],
                    ['min' => 50001, 'max' => 75000, 'fee' => 1000, 'type' => 'fixed'],
                    ['min' => 75001, 'max' => 100000, 'fee' => 1500, 'type' => 'fixed'],
                    ['min' => 100001, 'max' => 200000, 'fee' => 2000, 'type' => 'fixed'],
                    ['min' => 200001, 'max' => 300000, 'fee' => 3000, 'type' => 'fixed'],
                    ['min' => 300001, 'max' => 400000, 'fee' => 3500, 'type' => 'fixed'],
                    ['min' => 400001, 'max' => 500000, 'fee' => 3500, 'type' => 'fixed'],
                    ['min' => 500001, 'max' => 750000, 'fee' => 5000, 'type' => 'fixed'],
                    ['min' => 750001, 'max' => 1000000, 'fee' => 6000, 'type' => 'fixed'],
                    ['min' => 1000001, 'max' => 1500000, 'fee' => 8000, 'type' => 'fixed'],
                    ['min' => 1500001, 'max' => 2000000, 'fee' => 9900, 'type' => 'fixed'],
                    
                    // Envoi vers sous-région
                    ['min' => 1, 'max' => 2500, 'fee' => 100, 'type' => 'fixed'],
                    ['min' => 2501, 'max' => 5000, 'fee' => 100, 'type' => 'fixed'],
                    ['min' => 5001, 'max' => 10000, 'fee' => 400, 'type' => 'fixed'],
                    ['min' => 10001, 'max' => 20000, 'fee' => 450, 'type' => 'fixed'],
                    ['min' => 20001, 'max' => 25000, 'fee' => 500, 'type' => 'fixed'],
                    ['min' => 25001, 'max' => 50000, 'fee' => 950, 'type' => 'fixed'],
                    ['min' => 50001, 'max' => 75000, 'fee' => 1500, 'type' => 'fixed'],
                    ['min' => 75001, 'max' => 100000, 'fee' => 1550, 'type' => 'fixed'],
                    ['min' => 100001, 'max' => 150000, 'fee' => 2500, 'type' => 'fixed'],
                    ['min' => 150001, 'max' => 200000, 'fee' => 2550, 'type' => 'fixed'],
                    ['min' => 200001, 'max' => 270000, 'fee' => 3800, 'type' => 'fixed'],
                    ['min' => 270001, 'max' => 300000, 'fee' => 3800, 'type' => 'fixed'],
                    ['min' => 300001, 'max' => 400000, 'fee' => 5000, 'type' => 'fixed'],
                    ['min' => 400001, 'max' => 500000, 'fee' => 6200, 'type' => 'fixed'],
                    ['min' => 500001, 'max' => 700000, 'fee' => 7000, 'type' => 'fixed'],
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

            // Supprimer les anciens frais d'envoi pour cette méthode au Bénin
            $deleted = SendingFee::where('method_id', $method->id)
                ->where('country_code', 'BJ')
                ->delete();

            if ($deleted > 0) {
                $this->command->info("🗑️ {$deleted} ancien(s) palier(s) supprimé(s) pour {$method->name}");
            }

            // Insérer les nouveaux paliers
            foreach ($grid['tiers'] as $tier) {
                SendingFee::create([
                    'method_id' => $method->id,
                    'country_code' => 'BJ',
                    'min_amount' => $tier['min'],
                    'max_amount' => $tier['max'],
                    'fee_amount' => $tier['fee'],
                    'fee_type' => $tier['type'],
                    'method_name' => $method->name,
                ]);
                $totalInserted++;
            }

            $this->command->info("✅ " . count($grid['tiers']) . " paliers d'envoi importés pour {$method->name}");
        }

        $this->command->info("🎉 Importation terminée : {$totalInserted} paliers d'envoi insérés pour le Bénin !");
    }
}