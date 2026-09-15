<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AllCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // Afrique
            ['name' => 'Bénin', 'code' => 'BJ', 'currency' => 'XOF'],
            ['name' => 'Burkina Faso', 'code' => 'BF', 'currency' => 'XOF'],
            ['name' => 'Côte d\'Ivoire', 'code' => 'CI', 'currency' => 'XOF'],
            ['name' => 'Mali', 'code' => 'ML', 'currency' => 'XOF'],
            ['name' => 'Niger', 'code' => 'NE', 'currency' => 'XOF'],
            ['name' => 'Sénégal', 'code' => 'SN', 'currency' => 'XOF'],
            ['name' => 'Togo', 'code' => 'TG', 'currency' => 'XOF'],
            ['name' => 'Kenya', 'code' => 'KE', 'currency' => 'KES'],
            ['name' => 'Tanzanie', 'code' => 'TZ', 'currency' => 'TZS'],
            ['name' => 'Ouganda', 'code' => 'UG', 'currency' => 'UGX'],
            ['name' => 'Nigeria', 'code' => 'NG', 'currency' => 'NGN'],
            ['name' => 'Ghana', 'code' => 'GH', 'currency' => 'GHS'],
            ['name' => 'Guinée', 'code' => 'GN', 'currency' => 'GNF'],
            ['name' => 'République Démocratique du Congo', 'code' => 'CD', 'currency' => 'CDF'],
            ['name' => 'Madagascar', 'code' => 'MG', 'currency' => 'MGA'],
            ['name' => 'Maurice', 'code' => 'MU', 'currency' => 'MUR'],
            // Ajouter tous les autres pays d'Afrique...
            ['name' => 'Afrique du Sud', 'code' => 'ZA', 'currency' => 'ZAR'],
            ['name' => 'Algérie', 'code' => 'DZ', 'currency' => 'DZD'],
            ['name' => 'Angola', 'code' => 'AO', 'currency' => 'AOA'],
            ['name' => 'Botswana', 'code' => 'BW', 'currency' => 'BWP'],
            ['name' => 'Cameroun', 'code' => 'CM', 'currency' => 'XAF'],
            ['name' => 'Cap-Vert', 'code' => 'CV', 'currency' => 'CVE'],
            ['name' => 'Comores', 'code' => 'KM', 'currency' => 'KMF'],
            ['name' => 'Congo', 'code' => 'CG', 'currency' => 'XAF'],
            ['name' => 'Djibouti', 'code' => 'DJ', 'currency' => 'DJF'],
            ['name' => 'Égypte', 'code' => 'EG', 'currency' => 'EGP'],
            ['name' => 'Érythrée', 'code' => 'ER', 'currency' => 'ERN'],
            ['name' => 'Eswatini', 'code' => 'SZ', 'currency' => 'SZL'],
            ['name' => 'Éthiopie', 'code' => 'ET', 'currency' => 'ETB'],
            ['name' => 'Gabon', 'code' => 'GA', 'currency' => 'XAF'],
            ['name' => 'Gambie', 'code' => 'GM', 'currency' => 'GMD'],
            ['name' => 'Guinée-Bissau', 'code' => 'GW', 'currency' => 'XOF'],
            ['name' => 'Guinée équatoriale', 'code' => 'GQ', 'currency' => 'XAF'],
            ['name' => 'Lesotho', 'code' => 'LS', 'currency' => 'LSL'],
            ['name' => 'Liberia', 'code' => 'LR', 'currency' => 'LRD'],
            ['name' => 'Libye', 'code' => 'LY', 'currency' => 'LYD'],
            ['name' => 'Malawi', 'code' => 'MW', 'currency' => 'MWK'],
            ['name' => 'Maroc', 'code' => 'MA', 'currency' => 'MAD'],
            ['name' => 'Mauritanie', 'code' => 'MR', 'currency' => 'MRU'],
            ['name' => 'Mozambique', 'code' => 'MZ', 'currency' => 'MZN'],
            ['name' => 'Namibie', 'code' => 'NA', 'currency' => 'NAD'],
            ['name' => 'Ouganda', 'code' => 'UG', 'currency' => 'UGX'],
            ['name' => 'Rwanda', 'code' => 'RW', 'currency' => 'RWF'],
            ['name' => 'Sao Tomé-et-Principe', 'code' => 'ST', 'currency' => 'STN'],
            ['name' => 'Seychelles', 'code' => 'SC', 'currency' => 'SCR'],
            ['name' => 'Sierra Leone', 'code' => 'SL', 'currency' => 'SLE'],
            ['name' => 'Somalie', 'code' => 'SO', 'currency' => 'SOS'],
            ['name' => 'Soudan', 'code' => 'SD', 'currency' => 'SDG'],
            ['name' => 'Soudan du Sud', 'code' => 'SS', 'currency' => 'SSP'],
            ['name' => 'Tchad', 'code' => 'TD', 'currency' => 'XAF'],
            ['name' => 'Tunisie', 'code' => 'TN', 'currency' => 'TND'],
            ['name' => 'Zambie', 'code' => 'ZM', 'currency' => 'ZMW'],
            ['name' => 'Zimbabwe', 'code' => 'ZW', 'currency' => 'ZWL'],

            // Europe
            ['name' => 'France', 'code' => 'FR', 'currency' => 'EUR'],
            ['name' => 'Allemagne', 'code' => 'DE', 'currency' => 'EUR'],
            ['name' => 'Espagne', 'code' => 'ES', 'currency' => 'EUR'],
            ['name' => 'Italie', 'code' => 'IT', 'currency' => 'EUR'],
            ['name' => 'Royaume-Uni', 'code' => 'GB', 'currency' => 'GBP'],
            ['name' => 'Belgique', 'code' => 'BE', 'currency' => 'EUR'],
            ['name' => 'Suisse', 'code' => 'CH', 'currency' => 'CHF'],
            ['name' => 'Luxembourg', 'code' => 'LU', 'currency' => 'EUR'],
            ['name' => 'Pays-Bas', 'code' => 'NL', 'currency' => 'EUR'],
            ['name' => 'Portugal', 'code' => 'PT', 'currency' => 'EUR'],
            ['name' => 'Roumanie', 'code' => 'RO', 'currency' => 'RON'],
            ['name' => 'Grèce', 'code' => 'GR', 'currency' => 'EUR'],
            ['name' => 'Irlande', 'code' => 'IE', 'currency' => 'EUR'],
            ['name' => 'Autriche', 'code' => 'AT', 'currency' => 'EUR'],
            ['name' => 'Suède', 'code' => 'SE', 'currency' => 'SEK'],
            ['name' => 'Norvège', 'code' => 'NO', 'currency' => 'NOK'],
            ['name' => 'Danemark', 'code' => 'DK', 'currency' => 'DKK'],
            ['name' => 'Finlande', 'code' => 'FI', 'currency' => 'EUR'],
            ['name' => 'Pologne', 'code' => 'PL', 'currency' => 'PLN'],
            ['name' => 'Ukraine', 'code' => 'UA', 'currency' => 'UAH'],
            ['name' => 'Russie', 'code' => 'RU', 'currency' => 'RUB'],
            // Ajouter plus de pays ici...

            // Amériques
            ['name' => 'États-Unis', 'code' => 'US', 'currency' => 'USD'],
            ['name' => 'Canada', 'code' => 'CA', 'currency' => 'CAD'],
            ['name' => 'Mexique', 'code' => 'MX', 'currency' => 'MXN'],
            ['name' => 'Brésil', 'code' => 'BR', 'currency' => 'BRL'],
            ['name' => 'Argentine', 'code' => 'AR', 'currency' => 'ARS'],
            ['name' => 'Colombie', 'code' => 'CO', 'currency' => 'COP'],
            ['name' => 'Pérou', 'code' => 'PE', 'currency' => 'PEN'],
            ['name' => 'Chili', 'code' => 'CL', 'currency' => 'CLP'],
            ['name' => 'Venezuela', 'code' => 'VE', 'currency' => 'VES'],
            ['name' => 'Cuba', 'code' => 'CU', 'currency' => 'CUP'],
            ['name' => 'République dominicaine', 'code' => 'DO', 'currency' => 'DOP'],
            ['name' => 'Jamaïque', 'code' => 'JM', 'currency' => 'JMD'],
            ['name' => 'Trinité-et-Tobago', 'code' => 'TT', 'currency' => 'TTD'],
            ['name' => 'Panama', 'code' => 'PA', 'currency' => 'PAB'],
            ['name' => 'Costa Rica', 'code' => 'CR', 'currency' => 'CRC'],
            // Ajouter plus de pays ici...

            // Asie
            ['name' => 'Chine', 'code' => 'CN', 'currency' => 'CNY'],
            ['name' => 'Inde', 'code' => 'IN', 'currency' => 'INR'],
            ['name' => 'Japon', 'code' => 'JP', 'currency' => 'JPY'],
            ['name' => 'Corée du Sud', 'code' => 'KR', 'currency' => 'KRW'],
            ['name' => 'Singapour', 'code' => 'SG', 'currency' => 'SGD'],
            ['name' => 'Malaisie', 'code' => 'MY', 'currency' => 'MYR'],
            ['name' => 'Indonésie', 'code' => 'ID', 'currency' => 'IDR'],
            ['name' => 'Philippines', 'code' => 'PH', 'currency' => 'PHP'],
            ['name' => 'Thaïlande', 'code' => 'TH', 'currency' => 'THB'],
            ['name' => 'Vietnam', 'code' => 'VN', 'currency' => 'VND'],
            ['name' => 'Pakistan', 'code' => 'PK', 'currency' => 'PKR'],
            ['name' => 'Bangladesh', 'code' => 'BD', 'currency' => 'BDT'],
            ['name' => 'Turquie', 'code' => 'TR', 'currency' => 'TRY'],
            ['name' => 'Israël', 'code' => 'IL', 'currency' => 'ILS'],
            ['name' => 'Arabie saoudite', 'code' => 'SA', 'currency' => 'SAR'],
            ['name' => 'Émirats arabes unis', 'code' => 'AE', 'currency' => 'AED'],
            ['name' => 'Qatar', 'code' => 'QA', 'currency' => 'QAR'],
            ['name' => 'Koweït', 'code' => 'KW', 'currency' => 'KWD'],
            ['name' => 'Oman', 'code' => 'OM', 'currency' => 'OMR'],
            ['name' => 'Bahreïn', 'code' => 'BH', 'currency' => 'BHD'],
            ['name' => 'Liban', 'code' => 'LB', 'currency' => 'LBP'],
            ['name' => 'Jordanie', 'code' => 'JO', 'currency' => 'JOD'],
            ['name' => 'Irak', 'code' => 'IQ', 'currency' => 'IQD'],
            ['name' => 'Iran', 'code' => 'IR', 'currency' => 'IRR'],
            ['name' => 'Afghanistan', 'code' => 'AF', 'currency' => 'AFN'],
            ['name' => 'Népal', 'code' => 'NP', 'currency' => 'NPR'],
            ['name' => 'Sri Lanka', 'code' => 'LK', 'currency' => 'LKR'],
            ['name' => 'Myanmar', 'code' => 'MM', 'currency' => 'MMK'],
            ['name' => 'Cambodge', 'code' => 'KH', 'currency' => 'KHR'],
            ['name' => 'Laos', 'code' => 'LA', 'currency' => 'LAK'],
            // Ajouter plus de pays ici...

            // Océanie
            ['name' => 'Australie', 'code' => 'AU', 'currency' => 'AUD'],
            ['name' => 'Nouvelle-Zélande', 'code' => 'NZ', 'currency' => 'NZD'],
            ['name' => 'Papouasie-Nouvelle-Guinée', 'code' => 'PG', 'currency' => 'PGK'],
            ['name' => 'Fidji', 'code' => 'FJ', 'currency' => 'FJD'],
            ['name' => 'Îles Salomon', 'code' => 'SB', 'currency' => 'SBD'],
            ['name' => 'Vanuatu', 'code' => 'VU', 'currency' => 'VUV'],
            ['name' => 'Samoa', 'code' => 'WS', 'currency' => 'WST'],
            ['name' => 'Tonga', 'code' => 'TO', 'currency' => 'TOP'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'currency' => $country['currency'],
                    'is_active' => DB::raw('true'),
                ]
            );
        }

        $this->command->info('✅ Tous les pays ont été ajoutés avec succès.');
    }
}