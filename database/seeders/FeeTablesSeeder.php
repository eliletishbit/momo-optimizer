<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReceiptFee;
use App\Models\SendingFee;
use App\Models\Method;

class FeeTablesSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère les méthodes existantes (objets complets)
        $mtn = Method::where('code', 'mtn_bj')->first();
        $moov = Method::where('code', 'moov_bj')->first();
        $celtiis = Method::where('code', 'celtiis_bj')->first();

        if (!$mtn || !$moov || !$celtiis) {
            $this->command->error("Les méthodes MTN, Moov ou Celtiis sont introuvables dans la table 'methods'.");
            return;
        }

        // ---- FRAIS DE RETRAIT ----

        // MTN
        $mtnReceiptTiers = [
            [1, 500, 50], [501, 5000, 125], [5001, 10000, 225],
            [10001, 20000, 375], [20001, 50000, 700], [50001, 100000, 1000],
            [100001, 200000, 2000], [200001, 300000, 3000], [300001, 500000, 3500],
            [500001, 750000, 5000], [750001, 1000000, 6000], [1000001, 1500000, 8000],
            [1500001, 2000000, 9900],
        ];
        foreach ($mtnReceiptTiers as [$min, $max, $fee]) {
            ReceiptFee::updateOrCreate(
                [
                    'method_id' => $mtn->id,
                    'country_code' => 'BJ',
                    'min_amount' => $min,
                ],
                [
                    'max_amount' => $max,
                    'fee_amount' => $fee,
                    'fee_type' => 'fixed',
                    'method_name' => $mtn->name,  // ✅ Utilisation de l'objet
                ]
            );
        }

        // Moov
        $moovReceiptTiers = [
            [1, 500, 50], [501, 5000, 100], [5001, 10000, 200],
            [10001, 20000, 350], [20001, 50000, 700], [50001, 75000, 1000],
        ];
        foreach ($moovReceiptTiers as [$min, $max, $fee]) {
            ReceiptFee::updateOrCreate(
                [
                    'method_id' => $moov->id,
                    'country_code' => 'BJ',
                    'min_amount' => $min,
                ],
                [
                    'max_amount' => $max,
                    'fee_amount' => $fee,
                    'fee_type' => 'fixed',
                    'method_name' => $moov->name,
                ]
            );
        }

        // Celtiis
        $celtiisReceiptTiers = [
            [100, 500, 50], [501, 5000, 120], [5001, 10000, 200],
            [10001, 20000, 300], [20001, 50000, 600], [50001, 75000, 900],
            [75001, 100000, 1000], [100001, 200000, 2000], [200001, 300000, 3000],
            [300001, 500000, 3500], [500001, 750000, 5000], [750001, 1000000, 5800],
            [1000001, 1500000, 7800], [1500001, 2000000, 9800],
        ];
        foreach ($celtiisReceiptTiers as [$min, $max, $fee]) {
            ReceiptFee::updateOrCreate(
                [
                    'method_id' => $celtiis->id,
                    'country_code' => 'BJ',
                    'min_amount' => $min,
                ],
                [
                    'max_amount' => $max,
                    'fee_amount' => $fee,
                    'fee_type' => 'fixed',
                    'method_name' => $celtiis->name,
                ]
            );
        }

        // ---- FRAIS D'ENVOI ----

        SendingFee::updateOrCreate(
            ['method_id' => $mtn->id, 'country_code' => 'BJ'],
            [
                'min_amount' => 0,
                'max_amount' => 999999,
                'fee_amount' => 50,
                'fee_type' => 'fixed',
                'method_name' => $mtn->name,
            ]
        );

        SendingFee::updateOrCreate(
            ['method_id' => $moov->id, 'country_code' => 'BJ'],
            [
                'min_amount' => 0,
                'max_amount' => 999999,
                'fee_amount' => 50,
                'fee_type' => 'fixed',
                'method_name' => $moov->name,
            ]
        );

        SendingFee::updateOrCreate(
            ['method_id' => $celtiis->id, 'country_code' => 'BJ'],
            [
                'min_amount' => 0,
                'max_amount' => 999999,
                'fee_amount' => 0,
                'fee_type' => 'fixed',
                'method_name' => $celtiis->name,
            ]
        );

        $this->command->info('✅ Les grilles tarifaires ont été mises à jour avec succès.');
    }
}