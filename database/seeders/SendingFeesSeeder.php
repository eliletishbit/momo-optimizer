<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SendingFee;
use App\Models\Method;
use App\Models\Country;

class SendingFeesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Nettoyage de la table sending_fees...');
        SendingFee::truncate();

        $this->command->info('🔄 Ajout des frais d\'envoi...');

        // Récupérer les méthodes par code
        $methods = Method::whereRaw('is_active = true')->get()->keyBy('code');

        // Pays ciblés
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

            // Définir des paliers d'envoi pour ce réseau (exemple)
            $tiers = $this->getMobileMoneySendingTiers($code);

            foreach ($tiers as $tier) {
                SendingFee::create([
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
        $percentageMethods = [
            'wise' => ['fee' => 0.6, 'type' => 'percentage'], // 0.6% du montant
            'paypal' => ['fee' => 3.4, 'type' => 'percentage'], // 3.4% + fixe (voir après)
            'stripe' => ['fee' => 2.9, 'type' => 'percentage'], // 2.9% + 0.30
            'skrill' => ['fee' => 2.5, 'type' => 'percentage'],
            'neteller' => ['fee' => 2.5, 'type' => 'percentage'],
            'payoneer' => ['fee' => 1.0, 'type' => 'percentage'],
            'ofx' => ['fee' => 0.5, 'type' => 'percentage'],
            'transfergo' => ['fee' => 1.0, 'type' => 'percentage'],
            'azimo' => ['fee' => 1.5, 'type' => 'percentage'],
        ];

        foreach ($percentageMethods as $code => $config) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            // Pour chaque pays ciblé, on crée un enregistrement avec pourcentage
            foreach ($countries as $country) {
                // Exception : certains services ne sont pas disponibles partout (on simplifie)
                SendingFee::create([
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
            'westernunion' => ['fee' => 5.00, 'currency' => 'USD'],
            'moneygram' => ['fee' => 4.50, 'currency' => 'USD'],
            'ria' => ['fee' => 3.00, 'currency' => 'USD'],
            'xoom' => ['fee' => 4.99, 'currency' => 'USD'],
            'worldremit' => ['fee' => 3.00, 'currency' => 'USD'],
            'remitly' => ['fee' => 3.50, 'currency' => 'USD'],
            'smallworld' => ['fee' => 2.50, 'currency' => 'USD'],
        ];

        foreach ($fixedMethods as $code => $config) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            foreach ($countries as $country) {
                SendingFee::create([
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

        // ---- 4. FRAIS POUR BANQUES (frais de virement SEPA / SWIFT) ----
        $bankMethods = ['hsbc', 'bnp', 'deutschebank', 'citibank', 'barclays', 'societegenerale', 'ing', 'santander', 'bbva', 'bmo', 'tdbank', 'rbc', 'scotiabank', 'commonwealth', 'westpac', 'anz', 'nab'];

        foreach ($bankMethods as $code) {
            if (!isset($methods[$code])) continue;
            $method = $methods[$code];

            foreach ($countries as $country) {
                // Pour les virements, souvent un fixe + pourcentage, mais on simplifie ici
                SendingFee::create([
                    'method_id' => $method->id,
                    'country_code' => $country->code,
                    'min_amount' => 0,
                    'max_amount' => 999999,
                    'fee_amount' => 15.00, // Exemple : 15 EUR pour un virement SEPA
                    'fee_type' => 'fixed',
                    'method_name' => $method->name,
                ]);
                $inserted++;
            }
        }

        $this->command->info("✅ Frais d'envoi ajoutés : {$inserted} enregistrements.");
    }

    /**
     * Définit les paliers d'envoi pour les opérateurs Mobile Money
     */
    private function getMobileMoneySendingTiers(string $code): array
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
                ['min' => 20001, 'max' => 50000, 'fee' => 650, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 75000, 'fee' => 900, 'type' => 'fixed'],
            ],
            'celtiis_bj' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 120, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 300, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 600, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 75000, 'fee' => 900, 'type' => 'fixed'],
            ],
            'orange_ci' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 120, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 300, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 600, 'type' => 'fixed'],
                ['min' => 50001, 'max' => 100000, 'fee' => 1000, 'type' => 'fixed'],
            ],
            // M-Pesa Kenya (exemple)
            'mpesa_ke' => [
                ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                ['min' => 501, 'max' => 5000, 'fee' => 100, 'type' => 'fixed'],
                ['min' => 5001, 'max' => 10000, 'fee' => 200, 'type' => 'fixed'],
                ['min' => 10001, 'max' => 20000, 'fee' => 350, 'type' => 'fixed'],
                ['min' => 20001, 'max' => 50000, 'fee' => 650, 'type' => 'fixed'],
            ],
            // Par défaut pour les autres (exemple simple)
        ];

        return $tiers[$code] ?? [
            ['min' => 0, 'max' => 999999, 'fee' => 50, 'type' => 'fixed']
        ];
    }
}