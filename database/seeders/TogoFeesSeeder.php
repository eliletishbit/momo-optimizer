<?php

namespace Database\Seeders;

use App\Models\Method;
use App\Models\ReceiptFee;
use App\Models\SendingFee;
use Illuminate\Database\Seeder;

class TogoFeesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Début de l\'importation des frais pour le Togo (TG)...');

        $methods = Method::whereIn('code', ['tmoney_tg', 'moov_tg', 'mtn_tg'])->get();

        if ($methods->isEmpty()) {
            $this->command->warn('⚠️ Aucune méthode Togo trouvée avec les codes tmoney_tg, moov_tg, mtn_tg');
            return;
        }

        $receiptGrids = [
            'tmoney_tg' => [
                ['min' => 100, 'max' => 500, 'fee' => 50],
                ['min' => 501, 'max' => 5000, 'fee' => 125],
                ['min' => 5001, 'max' => 10000, 'fee' => 200],
                ['min' => 10001, 'max' => 20000, 'fee' => 350],
                ['min' => 20001, 'max' => 50000, 'fee' => 650],
                ['min' => 50001, 'max' => 100000, 'fee' => 950],
                ['min' => 100001, 'max' => 200000, 'fee' => 1900],
                ['min' => 200001, 'max' => 500000, 'fee' => 3200],
                ['min' => 500001, 'max' => 1000000, 'fee' => 5500],
                ['min' => 1000001, 'max' => 2000000, 'fee' => 9000],
            ],
            'moov_tg' => [
                ['min' => 100, 'max' => 500, 'fee' => 50],
                ['min' => 501, 'max' => 5000, 'fee' => 125],
                ['min' => 5001, 'max' => 10000, 'fee' => 220],
                ['min' => 10001, 'max' => 20000, 'fee' => 375],
                ['min' => 20001, 'max' => 50000, 'fee' => 700],
                ['min' => 50001, 'max' => 100000, 'fee' => 1000],
                ['min' => 100001, 'max' => 200000, 'fee' => 2000],
                ['min' => 200001, 'max' => 500000, 'fee' => 3500],
                ['min' => 500001, 'max' => 1000000, 'fee' => 5800],
                ['min' => 1000001, 'max' => 2000000, 'fee' => 9500],
            ],
            'mtn_tg' => [
                ['min' => 100, 'max' => 500, 'fee' => 50],
                ['min' => 501, 'max' => 5000, 'fee' => 130],
                ['min' => 5001, 'max' => 10000, 'fee' => 230],
                ['min' => 10001, 'max' => 20000, 'fee' => 380],
                ['min' => 20001, 'max' => 50000, 'fee' => 700],
                ['min' => 50001, 'max' => 100000, 'fee' => 1000],
                ['min' => 100001, 'max' => 200000, 'fee' => 2000],
                ['min' => 200001, 'max' => 500000, 'fee' => 3500],
                ['min' => 500001, 'max' => 1000000, 'fee' => 6000],
                ['min' => 1000001, 'max' => 2000000, 'fee' => 9800],
            ],
        ];

        $sendingGrids = [
            'tmoney_tg' => [
                ['min' => 100, 'max' => 5000, 'fee' => 25],
                ['min' => 5001, 'max' => 20000, 'fee' => 50],
                ['min' => 20001, 'max' => 50000, 'fee' => 100],
                ['min' => 50001, 'max' => 100000, 'fee' => 200],
                ['min' => 100001, 'max' => 2000000, 'fee' => 500],
            ],
            'moov_tg' => [
                ['min' => 100, 'max' => 5000, 'fee' => 25],
                ['min' => 5001, 'max' => 20000, 'fee' => 50],
                ['min' => 20001, 'max' => 50000, 'fee' => 100],
                ['min' => 50001, 'max' => 100000, 'fee' => 250],
                ['min' => 100001, 'max' => 2000000, 'fee' => 500],
            ],
            'mtn_tg' => [
                ['min' => 100, 'max' => 5000, 'fee' => 30],
                ['min' => 5001, 'max' => 20000, 'fee' => 60],
                ['min' => 20001, 'max' => 50000, 'fee' => 120],
                ['min' => 50001, 'max' => 100000, 'fee' => 250],
                ['min' => 100001, 'max' => 2000000, 'fee' => 500],
            ],
        ];

        foreach ($methods as $method) {
            $code = $method->code;

            if (isset($receiptGrids[$code])) {
                ReceiptFee::where('method_id', $method->id)->where('country_code', 'TG')->delete();
                foreach ($receiptGrids[$code] as $tier) {
                    ReceiptFee::create([
                        'method_id' => $method->id,
                        'country_code' => 'TG',
                        'min_amount' => $tier['min'],
                        'max_amount' => $tier['max'],
                        'fee_amount' => $tier['fee'],
                        'fee_type' => 'fixed',
                        'method_name' => $method->name,
                    ]);
                }
                $this->command->info("✅ Paliers de retrait ajoutés pour {$method->name}");
            }

            if (isset($sendingGrids[$code])) {
                SendingFee::where('method_id', $method->id)->where('country_code', 'TG')->delete();
                foreach ($sendingGrids[$code] as $tier) {
                    SendingFee::create([
                        'method_id' => $method->id,
                        'country_code' => 'TG',
                        'min_amount' => $tier['min'],
                        'max_amount' => $tier['max'],
                        'fee_amount' => $tier['fee'],
                        'fee_type' => 'fixed',
                        'method_name' => $method->name,
                    ]);
                }
                $this->command->info("✅ Paliers d'envoi ajoutés pour {$method->name}");
            }
        }

        $this->command->info('🎉 Seed des frais du Togo terminé avec succès !');
    }
}