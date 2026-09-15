<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReceiptFee;
use App\Models\Method;
use App\Models\Country;

class ReceiptFeesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Nettoyage de la table receipt_fees...');
        ReceiptFee::truncate();

        $this->command->info('🔄 Ajout des frais de réception (retrait)...');

        // Récupérer les méthodes par code
        $methods = Method::whereRaw('is_active = true')->get()->keyBy('code');

        // Pays ciblés (même liste que pour l'envoi)
        $countryCodes = [
            // Afrique
            'BJ', 'CI', 'SN', 'TG', 'BF', 'ML', 'NE', 'GH', 'NG',
            'KE', 'TZ', 'UG', 'RW', 'ZA', 'ZM', 'MZ',
            'MA', 'TN', 'DZ', 'EG',
            // Europe
            'FR', 'DE', 'ES', 'IT', 'GB', 'BE', 'CH', 'LU', 'NL', 'PT',
            'RO', 'GR', 'AT', 'SE', 'NO', 'DK', 'FI', 'PL', 'IE', 'CZ',
            // Amériques
            'US', 'CA', 'MX', 'BR', 'AR', 'CO', 'CL',
            // Asie & Océanie
            'CN', 'IN', 'JP', 'SG', 'MY', 'AU', 'NZ', 'AE', 'SA', 'IL', 'TR',
        ];

        $countries = Country::whereIn('code', $countryCodes)->whereRaw('is_active = true')->get();

        if ($countries->isEmpty()) {
            $this->command->error('❌ Aucun pays trouvé dans la liste ciblée.');
            return;
        }

        $inserted = 0;

        // ---- 1. FRAIS FIXES PAR PALIERS (Afrique Mobile Money) ----
        $mobileMoneyMethods = ['mtn_bj', 'moov_bj', 'celtiis_bj', 'orange_ci', 'orange_sn', 'mpesa_ke', 'mpesa_tz', 'airtel_ng', 'tigo_gh', 'vodafone_gh'];

        foreach ($mobileMoneyMethods as $code) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];
            $countryCode = $method->country_code;

            // Définir des paliers de retrait pour ce réseau (exemple)
            $tiers = $this->getMobileMoneyReceiptTiers($code);

            foreach ($tiers as $tier) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => $countryCode,
                    'min_amount' => $tier['min'],
                    'max_amount' => $tier['max'],
                    'fee_amount' => $tier['fee'],
                    'fee_type' => $tier['type'],
                    'method_name' => $method->name,
                ]);
                $inserted++;
            }
        }

        // ---- 2. FRAIS EN POURCENTAGE (Services internationaux) ----
        // Note : pour les retraits internationaux, certains appliquent un pourcentage
        $percentageMethods = [
            'wise' => ['fee' => 0.5, 'type' => 'percentage'], // 0.5% du montant retiré
            'paypal' => ['fee' => 2.5, 'type' => 'percentage'], // 2.5% + fixe (simplifié)
            'stripe' => ['fee' => 2.0, 'type' => 'percentage'],
            'skrill' => ['fee' => 2.0, 'type' => 'percentage'],
            'neteller' => ['fee' => 2.0, 'type' => 'percentage'],
            'payoneer' => ['fee' => 1.0, 'type' => 'percentage'],
            'ofx' => ['fee' => 0.5, 'type' => 'percentage'],
        ];

        foreach ($percentageMethods as $code => $config) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            // Pour chaque pays ciblé
            foreach ($countries as $country) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => $country->code,
                    'min_amount' => 0,
                    'max_amount' => 999999,
                    'fee_amount' => $config['fee'],
                    'fee_type' => $config['type'],
                    'method_name' => $method->name,
                ]);
                $inserted++;
            }
        }

        // ---- 3. FRAIS FIXES UNIQUES (Western Union, MoneyGram, etc.) ----
        $fixedMethods = [
            'westernunion' => ['fee' => 5.00],
            'moneygram' => ['fee' => 4.50],
            'ria' => ['fee' => 3.00],
            'xoom' => ['fee' => 4.99],
            'worldremit' => ['fee' => 3.00],
            'remitly' => ['fee' => 3.50],
            'smallworld' => ['fee' => 2.50],
        ];

        foreach ($fixedMethods as $code => $config) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            foreach ($countries as $country) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => $country->code,
                    'min_amount' => 0,
                    'max_amount' => 999999,
                    'fee_amount' => $config['fee'],
                    'fee_type' => 'fixed',
                    'method_name' => $method->name,
                ]);
                $inserted++;
            }
        }

        // ---- 4. FRAIS POUR BANQUES (frais de retrait) ----
        $bankMethods = ['hsbc', 'bnp', 'deutschebank', 'citibank', 'barclays', 'societegenerale', 'ing', 'santander', 'bbva', 'bmo', 'tdbank', 'rbc', 'scotiabank', 'commonwealth', 'westpac', 'anz', 'nab'];

        foreach ($bankMethods as $code) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            foreach ($countries as $country) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => $country->code,
                    'min_amount' => 0,
                    'max_amount' => 999999,
                    'fee_amount' => 10.00, // Exemple : 10 EUR pour un retrait en agence
                    'fee_type' => 'fixed',
                    'method_name' => $method->name,
                ]);
                $inserted++;
            }
        }

        $this->command->info("✅ Frais de réception ajoutés : {$inserted} enregistrements.");
    }

    /**
     * Définit les paliers de retrait pour les opérateurs Mobile Money
     */
    private function getMobileMoneyReceiptTiers(string $code): array
    {
        // Exemple de paliers pour MTN Bénin (frais fixes)
        $tiers = [
            'mtn_bj' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 125, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 225, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 375, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 700, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 100000, 'fee' => 1000, 'type' => 'fixed'],
                ['min' => 100001, 'max' => 200000, 'fee' => 2000, 'type' => 'fixed'],
            ],
            'moov_bj' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 100, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 350, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 700, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 75000, 'fee' => 1000, 'type' => 'fixed'],
            ],
            'celtiis_bj' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 120, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 300, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 600, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 75000, 'fee' => 900, 'type' => 'fixed'],
                ['min' => 75001, 'max' => 100000, 'fee' => 1000, 'type' => 'fixed'],
                ['min' => 100001, 'max' => 200000, 'fee' => 2000, 'type' => 'fixed'],
            ],
            'orange_ci' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 120, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 300, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 600, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 100000, 'fee' => 1000, 'type' => 'fixed'],
            ],
            'mpesa_ke' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 100, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 350, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 650, 'type' => 'fixed'],
            ],
        ];

        return $tiers[$code] ?? [
            ['min' => 0, 'max' => 999999, 'fee' => 50, 'type' => 'fixed']
        ];
    }
}