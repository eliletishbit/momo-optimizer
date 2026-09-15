<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'MTN MoMo', 'code' => 'mtn_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => null, 'color_primary' => '#F5A623', 'currency' => 'XOF', 'is_active' => true],
            ['name' => 'Moov Money', 'code' => 'moov_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => null, 'color_primary' => '#0056A4', 'currency' => 'XOF', 'is_active' => true],
            ['name' => 'Celtiis Cash', 'code' => 'celtiis_bj', 'category' => 'mobile_money', 'country_code' => 'BJ', 'logo_url' => null, 'color_primary' => '#00A651', 'currency' => 'XOF', 'is_active' => true],
            ['name' => 'Orange Money', 'code' => 'orange_ci', 'category' => 'mobile_money', 'country_code' => 'CI', 'logo_url' => null, 'color_primary' => '#FF6600', 'currency' => 'XOF', 'is_active' => true],
            ['name' => 'Wise', 'code' => 'wise', 'category' => 'international', 'country_code' => null, 'logo_url' => null, 'color_primary' => '#00B4AB', 'currency' => 'EUR', 'is_active' => true],
            ['name' => 'PayPal', 'code' => 'paypal', 'category' => 'international', 'country_code' => null, 'logo_url' => null, 'color_primary' => '#003087', 'currency' => 'USD', 'is_active' => true],
            ['name' => 'Ecobank', 'code' => 'ecobank', 'category' => 'bank', 'country_code' => 'BJ', 'logo_url' => null, 'color_primary' => '#1F3A5F', 'currency' => 'XOF', 'is_active' => true],
            ['name' => 'Banque Atlantique', 'code' => 'banque_atlantique', 'category' => 'bank', 'country_code' => 'BJ', 'logo_url' => null, 'color_primary' => '#0068A5', 'currency' => 'XOF', 'is_active' => true],
        ];

        foreach ($methods as $methodData) {
            $method = Method::firstOrNew(['code' => $methodData['code']]);
            $method->fill(array_merge($methodData, ['id' => $method->id ?? (string) Str::uuid()]));
            $method->save();
        }
    }
}
