<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Method;
use Illuminate\Support\Facades\DB;

class AllMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Ajout des méthodes de paiement...');

        $methods = [
            // ============================================================
            // 1. MOBILE MONEY AFRIQUE (COMPLET)
            // ============================================================
            // Bénin
            ['name' => 'MTN MoMo (Bénin)', 'code' => 'mtn_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XOF'],
            ['name' => 'Moov Money (Bénin)', 'code' => 'moov_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => 'https://www.moov-africa.bj/static/moov-logo.png', 'color_primary' => '#0056A4', 'currency' => 'XOF'],
            ['name' => 'Celtiis Cash (Bénin)', 'code' => 'celtiis_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => 'https://www.celtiis.com/static/celtiis-logo.png', 'color_primary' => '#00A651', 'currency' => 'XOF'],

            // Togo
            ['name' => 'MTN MoMo (Togo)', 'code' => 'mtn_tg', 'category' => 'mobile_money', 'country_code' => 'TG', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XOF'],
            ['name' => 'Moov Money (Togo)', 'code' => 'moov_tg', 'category' => 'mobile_money', 'country_code' => 'TG', 'logo_url' => 'https://www.moov-africa.bj/static/moov-logo.png', 'color_primary' => '#0056A4', 'currency' => 'XOF'],
            ['name' => 'T-Money (Togo)', 'code' => 'tmoney_tg', 'category' => 'mobile_money', 'country_code' => 'TG', 'logo_url' => 'https://www.togocom.tg/static/tmoney-logo.png', 'color_primary' => '#E60000', 'currency' => 'XOF'],

            // Côte d'Ivoire
            ['name' => 'MTN MoMo (Côte d\'Ivoire)', 'code' => 'mtn_ci', 'category' => 'mobile_money', 'country_code' => 'CI', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XOF'],
            ['name' => 'Moov Money (Côte d\'Ivoire)', 'code' => 'moov_ci', 'category' => 'mobile_money', 'country_code' => 'CI', 'logo_url' => 'https://www.moov-africa.ci/static/moov-logo.png', 'color_primary' => '#0056A4', 'currency' => 'XOF'],
            ['name' => 'Orange Money (Côte d\'Ivoire)', 'code' => 'orange_ci', 'category' => 'mobile_money', 'country_code' => 'CI', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XOF'],

            // Sénégal
            ['name' => 'Orange Money (Sénégal)', 'code' => 'orange_sn', 'category' => 'mobile_money', 'country_code' => 'SN', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XOF'],
            ['name' => 'Wave (Sénégal)', 'code' => 'wave_sn', 'category' => 'mobile_money', 'country_code' => 'SN', 'logo_url' => 'https://wave.com/static/wave-logo.png', 'color_primary' => '#00A3E0', 'currency' => 'XOF'],
            ['name' => 'Free Money (Sénégal)', 'code' => 'free_sn', 'category' => 'mobile_money', 'country_code' => 'SN', 'logo_url' => 'https://www.free.fr/static/free-money-logo.png', 'color_primary' => '#E60000', 'currency' => 'XOF'],

            // Mali
            ['name' => 'Orange Money (Mali)', 'code' => 'orange_ml', 'category' => 'mobile_money', 'country_code' => 'ML', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XOF'],
            ['name' => 'MTN MoMo (Mali)', 'code' => 'mtn_ml', 'category' => 'mobile_money', 'country_code' => 'ML', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XOF'],

            // Burkina Faso
            ['name' => 'Orange Money (Burkina)', 'code' => 'orange_bf', 'category' => 'mobile_money', 'country_code' => 'BF', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XOF'],
            ['name' => 'Moov Money (Burkina)', 'code' => 'moov_bf', 'category' => 'mobile_money', 'country_code' => 'BF', 'logo_url' => 'https://www.moov-africa.bj/static/moov-logo.png', 'color_primary' => '#0056A4', 'currency' => 'XOF'],

            // Niger
            ['name' => 'Orange Money (Niger)', 'code' => 'orange_ne', 'category' => 'mobile_money', 'country_code' => 'NE', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XOF'],
            ['name' => 'MTN MoMo (Niger)', 'code' => 'mtn_ne', 'category' => 'mobile_money', 'country_code' => 'NE', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XOF'],

            // Ghana
            ['name' => 'MTN MoMo (Ghana)', 'code' => 'mtn_gh', 'category' => 'mobile_money', 'country_code' => 'GH', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'GHS'],
            ['name' => 'Vodafone Cash (Ghana)', 'code' => 'vodafone_gh', 'category' => 'mobile_money', 'country_code' => 'GH', 'logo_url' => 'https://www.vodafone.com.gh/static/vodafone-cash-logo.png', 'color_primary' => '#D40000', 'currency' => 'GHS'],
            ['name' => 'Tigo Money (Ghana)', 'code' => 'tigo_gh', 'category' => 'mobile_money', 'country_code' => 'GH', 'logo_url' => 'https://www.tigo.com.gh/static/tigo-money-logo.png', 'color_primary' => '#004B87', 'currency' => 'GHS'],

            // Nigeria
            ['name' => 'MTN MoMo (Nigeria)', 'code' => 'mtn_ng', 'category' => 'mobile_money', 'country_code' => 'NG', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'NGN'],
            ['name' => 'Airtel Money (Nigeria)', 'code' => 'airtel_ng', 'category' => 'mobile_money', 'country_code' => 'NG', 'logo_url' => 'https://www.airtel.com/static/airtel-money-logo.png', 'color_primary' => '#D40000', 'currency' => 'NGN'],

            // Kenya
            ['name' => 'M-Pesa (Kenya)', 'code' => 'mpesa_ke', 'category' => 'mobile_money', 'country_code' => 'KE', 'logo_url' => 'https://www.m-pesa.com/static/mpesa-logo.png', 'color_primary' => '#009B77', 'currency' => 'KES'],

            // Tanzanie
            ['name' => 'M-Pesa (Tanzanie)', 'code' => 'mpesa_tz', 'category' => 'mobile_money', 'country_code' => 'TZ', 'logo_url' => 'https://www.m-pesa.com/static/mpesa-logo.png', 'color_primary' => '#009B77', 'currency' => 'TZS'],
            ['name' => 'Tigo Pesa (Tanzanie)', 'code' => 'tigo_tz', 'category' => 'mobile_money', 'country_code' => 'TZ', 'logo_url' => 'https://www.tigo.com/static/tigo-logo.png', 'color_primary' => '#0056A4', 'currency' => 'TZS'],

            // Ouganda
            ['name' => 'MTN MoMo (Ouganda)', 'code' => 'mtn_ug', 'category' => 'mobile_money', 'country_code' => 'UG', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'UGX'],
            ['name' => 'Airtel Money (Ouganda)', 'code' => 'airtel_ug', 'category' => 'mobile_money', 'country_code' => 'UG', 'logo_url' => 'https://www.airtel.com/static/airtel-money-logo.png', 'color_primary' => '#D40000', 'currency' => 'UGX'],

            // Rwanda
            ['name' => 'MTN MoMo (Rwanda)', 'code' => 'mtn_rw', 'category' => 'mobile_money', 'country_code' => 'RW', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'RWF'],

            // Zambie
            ['name' => 'MTN MoMo (Zambie)', 'code' => 'mtn_zm', 'category' => 'mobile_money', 'country_code' => 'ZM', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'ZMW'],

            // Cameroun
            ['name' => 'MTN MoMo (Cameroun)', 'code' => 'mtn_cm', 'category' => 'mobile_money', 'country_code' => 'CM', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'XAF'],
            ['name' => 'Orange Money (Cameroun)', 'code' => 'orange_cm', 'category' => 'mobile_money', 'country_code' => 'CM', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'XAF'],

            // Botswana
            ['name' => 'Orange Money (Botswana)', 'code' => 'orange_bw', 'category' => 'mobile_money', 'country_code' => 'BW', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'BWP'],

            // Guinée
            ['name' => 'Orange Money (Guinée)', 'code' => 'orange_gn', 'category' => 'mobile_money', 'country_code' => 'GN', 'logo_url' => 'https://www.orange.com/static/orange-money-logo.png', 'color_primary' => '#FF6600', 'currency' => 'GNF'],
            ['name' => 'MTN MoMo (Guinée)', 'code' => 'mtn_gn', 'category' => 'mobile_money', 'country_code' => 'GN', 'logo_url' => 'https://www.mtn.ci/static/momo-logo.png', 'color_primary' => '#F5A623', 'currency' => 'GNF'],

            // Malawi
            ['name' => 'Airtel Money (Malawi)', 'code' => 'airtel_mw', 'category' => 'mobile_money', 'country_code' => 'MW', 'logo_url' => 'https://www.airtel.com/static/airtel-money-logo.png', 'color_primary' => '#D40000', 'currency' => 'MWK'],

            // Éthiopie
            ['name' => 'TeleBirr (Éthiopie)', 'code' => 'telebirr_et', 'category' => 'mobile_money', 'country_code' => 'ET', 'logo_url' => 'https://www.ethiotelecom.et/static/telebirr-logo.png', 'color_primary' => '#00A651', 'currency' => 'ETB'],

            // Zimbabwe
            ['name' => 'EcoCash (Zimbabwe)', 'code' => 'ecocash_zw', 'category' => 'mobile_money', 'country_code' => 'ZW', 'logo_url' => 'https://www.econet.co.zw/static/ecocash-logo.png', 'color_primary' => '#0056A4', 'currency' => 'ZWL'],

            // ============================================================
            // 2. BANQUES PANAFRICAINES & LOCALES
            // ============================================================
            ['name' => 'Ecobank', 'code' => 'ecobank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.ecobank.com/static/ecobank-logo.png', 'color_primary' => '#0056A4', 'currency' => 'XOF'],
            ['name' => 'Bank of Africa (BMCE)', 'code' => 'boa', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.bank-of-africa.com/static/boa-logo.png', 'color_primary' => '#004B87', 'currency' => 'XOF'],
            ['name' => 'Attijariwafa Bank', 'code' => 'attijariwafa', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.attijariwafabank.com/static/attijari-logo.png', 'color_primary' => '#0066CC', 'currency' => 'MAD'],
            ['name' => 'Access Bank', 'code' => 'access', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.accessbankplc.com/static/access-logo.png', 'color_primary' => '#009B77', 'currency' => 'NGN'],
            ['name' => 'Banque Atlantique', 'code' => 'atlantique', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.banqueatlantique.net/static/atlantique-logo.png', 'color_primary' => '#003366', 'currency' => 'XOF'],
            ['name' => 'Zenith Bank', 'code' => 'zenith', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.zenithbank.com/static/zenith-logo.png', 'color_primary' => '#D40000', 'currency' => 'NGN'],
            ['name' => 'United Bank for Africa (UBA)', 'code' => 'uba', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.ubagroup.com/static/uba-logo.png', 'color_primary' => '#004B87', 'currency' => 'NGN'],
            ['name' => 'First Bank of Nigeria', 'code' => 'firstbank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.firstbanknigeria.com/static/firstbank-logo.png', 'color_primary' => '#003366', 'currency' => 'NGN'],
            ['name' => 'Guaranty Trust Bank (GTBank)', 'code' => 'gtbank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.gtbank.com/static/gtbank-logo.png', 'color_primary' => '#FF6C00', 'currency' => 'NGN'],
            ['name' => 'Banque Internationale du Bénin (BIBE)', 'code' => 'bibe', 'category' => 'bank', 'country_code' => 'BJ', 'logo_url' => 'https://www.bibe.com/static/bibe-logo.png', 'color_primary' => '#005C9E', 'currency' => 'XOF'],

            // ============================================================
            // 3. SERVICES INTERNATIONAUX (Transferts & Portefeuilles)
            // ============================================================
            ['name' => 'Wise', 'code' => 'wise', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://wise.com/public-resources/assets/logos/wise-logo.svg', 'color_primary' => '#00B4AB', 'currency' => 'EUR'],
            ['name' => 'WorldRemit', 'code' => 'worldremit', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.worldremit.com/_next/static/media/wr-logo.8c8f1f12.svg', 'color_primary' => '#00A3E0', 'currency' => 'USD'],
            ['name' => 'Remitly', 'code' => 'remitly', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.remitly.com/images/logo-remitly.svg', 'color_primary' => '#4A90D9', 'currency' => 'USD'],
            ['name' => 'Payoneer', 'code' => 'payoneer', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.payoneer.com/content/dam/payoneer/images/logos/payoneer-logo.svg', 'color_primary' => '#FF6C00', 'currency' => 'USD'],
            ['name' => 'Western Union', 'code' => 'westernunion', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.westernunion.com/etc/designs/wu/clientlibs/img/wu-logo.svg', 'color_primary' => '#FFCD00', 'currency' => 'USD'],
            ['name' => 'MoneyGram', 'code' => 'moneygram', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.moneygram.com/etc/designs/mg/images/moneygram-logo.svg', 'color_primary' => '#D40000', 'currency' => 'USD'],
            ['name' => 'Ria', 'code' => 'ria', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.riafinancial.com/static/images/ria-logo.svg', 'color_primary' => '#E31937', 'currency' => 'USD'],
            ['name' => 'Xoom (PayPal)', 'code' => 'xoom', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.xoom.com/static/img/logo-xoom.svg', 'color_primary' => '#003087', 'currency' => 'USD'],
            ['name' => 'TransferGo', 'code' => 'transfergo', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://transfergo.com/static/images/logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'EUR'],
            ['name' => 'Azimo', 'code' => 'azimo', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://azimo.com/static/images/azimo-logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'EUR'],
            ['name' => 'Small World', 'code' => 'smallworld', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.smallworldfs.com/wp-content/themes/smallworld/images/logo.svg', 'color_primary' => '#E31937', 'currency' => 'EUR'],
            ['name' => 'OFX', 'code' => 'ofx', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.ofx.com/-/media/images/ofx-logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'USD'],

            // Portefeuilles électroniques
            ['name' => 'PayPal', 'code' => 'paypal', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.paypalobjects.com/webstatic/i/logo/rebrand/ppcom.svg', 'color_primary' => '#003087', 'currency' => 'USD'],
            ['name' => 'Stripe', 'code' => 'stripe', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://stripe.com/img/v3/about/logo-dark.svg', 'color_primary' => '#635BFF', 'currency' => 'USD'],
            ['name' => 'Skrill', 'code' => 'skrill', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.skrill.com/fileadmin/templates/responsive/images/skrill_logo.svg', 'color_primary' => '#1B1B1B', 'currency' => 'EUR'],
            ['name' => 'Neteller', 'code' => 'neteller', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.neteller.com/fr/assets/images/neteller-logo.svg', 'color_primary' => '#0079C2', 'currency' => 'USD'],
            ['name' => 'Perfect Money', 'code' => 'perfectmoney', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://perfectmoney.com/images/logo.png', 'color_primary' => '#0088CC', 'currency' => 'USD'],
            ['name' => 'Revolut', 'code' => 'revolut', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.revolut.com/static/logo-revolut.svg', 'color_primary' => '#0077B6', 'currency' => 'EUR'],
            ['name' => 'N26', 'code' => 'n26', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://n26.com/images/n26-logo.svg', 'color_primary' => '#000000', 'currency' => 'EUR'],
            ['name' => 'Monese', 'code' => 'monese', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://monese.com/static/images/monese-logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'EUR'],
            ['name' => 'Bunq', 'code' => 'bunq', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.bunq.com/static/bunq-logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'EUR'],
            ['name' => 'Klarna', 'code' => 'klarna', 'category' => 'international', 'country_code' => null, 'logo_url' => 'https://www.klarna.com/static/klarna-logo.svg', 'color_primary' => '#FFB3C7', 'currency' => 'EUR'],

            // ============================================================
            // 4. BANQUES INTERNATIONALES
            // ============================================================
            ['name' => 'HSBC', 'code' => 'hsbc', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.hsbc.com/-/media/hsbc/images/logo.svg', 'color_primary' => '#DB0011', 'currency' => 'USD'],
            ['name' => 'BNP Paribas', 'code' => 'bnp', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.bnpparibas.com/sites/default/files/logo-bnp-paribas.png', 'color_primary' => '#009B77', 'currency' => 'EUR'],
            ['name' => 'Deutsche Bank', 'code' => 'deutschebank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.deutsche-bank.de/content/dam/deutsche-bank/logo.svg', 'color_primary' => '#003366', 'currency' => 'EUR'],
            ['name' => 'Citibank', 'code' => 'citibank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.citibank.com/logo/citibank-logo.svg', 'color_primary' => '#004B87', 'currency' => 'USD'],
            ['name' => 'Barclays', 'code' => 'barclays', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.barclays.com/content/dam/barclays/logo.svg', 'color_primary' => '#00A3E0', 'currency' => 'GBP'],
            ['name' => 'Standard Chartered', 'code' => 'standardchartered', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.sc.com/static/images/logo.svg', 'color_primary' => '#001A33', 'currency' => 'USD'],
            ['name' => 'Société Générale', 'code' => 'societegenerale', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.societegenerale.com/sites/default/files/logo-sg.svg', 'color_primary' => '#0066CC', 'currency' => 'EUR'],
            ['name' => 'Crédit Agricole', 'code' => 'creditagricole', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.credit-agricole.fr/static/images/logo-ca.svg', 'color_primary' => '#005C9E', 'currency' => 'EUR'],
            ['name' => 'ING', 'code' => 'ing', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.ing.com/static/logo/ing-logo.svg', 'color_primary' => '#FF6200', 'currency' => 'EUR'],
            ['name' => 'Santander', 'code' => 'santander', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.santander.com/static/images/logo-santander.svg', 'color_primary' => '#EC0000', 'currency' => 'EUR'],
            ['name' => 'BBVA', 'code' => 'bbva', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.bbva.com/static/images/logo-bbva.svg', 'color_primary' => '#072146', 'currency' => 'EUR'],
            ['name' => 'BMO', 'code' => 'bmo', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.bmo.com/static/images/logo-bmo.svg', 'color_primary' => '#003366', 'currency' => 'CAD'],
            ['name' => 'TD Bank', 'code' => 'tdbank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.td.com/static/images/logo-td.svg', 'color_primary' => '#006600', 'currency' => 'CAD'],
            ['name' => 'RBC', 'code' => 'rbc', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.rbc.com/static/images/logo-rbc.svg', 'color_primary' => '#003366', 'currency' => 'CAD'],
            ['name' => 'Scotiabank', 'code' => 'scotiabank', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.scotiabank.com/static/images/logo-scotiabank.svg', 'color_primary' => '#004B87', 'currency' => 'CAD'],
            ['name' => 'Commonwealth Bank', 'code' => 'commonwealth', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.commbank.com.au/static/images/logo-commbank.svg', 'color_primary' => '#D40000', 'currency' => 'AUD'],
            ['name' => 'Westpac', 'code' => 'westpac', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.westpac.com.au/static/images/logo-westpac.svg', 'color_primary' => '#D40000', 'currency' => 'AUD'],
            ['name' => 'ANZ', 'code' => 'anz', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.anz.com/static/images/logo-anz.svg', 'color_primary' => '#003366', 'currency' => 'AUD'],
            ['name' => 'NAB', 'code' => 'nab', 'category' => 'bank', 'country_code' => null, 'logo_url' => 'https://www.nab.com.au/static/images/logo-nab.svg', 'color_primary' => '#003366', 'currency' => 'AUD'],
        ];

        // Ajout de la valeur is_active avec DB::raw('true')
        foreach ($methods as $method) {
            $method['is_active'] = DB::raw('true');
            Method::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('✅ Toutes les méthodes de paiement ont été ajoutées/mises à jour avec succès.');
    }
}