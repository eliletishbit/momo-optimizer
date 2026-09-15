<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Method;
use App\Models\ReceiptFee;
use App\Services\FeeOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeOptimizerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name' => 'Bénin',
            'code' => 'BJ',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        $moov = Method::create([
            'name' => 'Moov',
            'code' => 'moov',
            'category' => 'mobile_money',
            'country_code' => $country->code,
            'logo_url' => 'https://example.com/moov.png',
            'is_active' => true,
        ]);

        $celtiis = Method::create([
            'name' => 'Celtiis',
            'code' => 'celtiis',
            'category' => 'mobile_money',
            'country_code' => $country->code,
            'logo_url' => 'https://example.com/celtiis.png',
            'is_active' => true,
        ]);

        $ecobank = Method::create([
            'name' => 'Ecobank',
            'code' => 'ecobank',
            'category' => 'bank',
            'country_code' => $country->code,
            'logo_url' => 'https://example.com/ecobank.png',
            'is_active' => true,
        ]);

        $feeMatrix = [
            'Moov' => [
                [1, 500, 50],
                [501, 5000, 100],
                [5001, 10000, 200],
                [10001, 20000, 350],
                [20001, 50000, 650],
                [50001, 100000, 900],
                [100001, 200000, 1750],
            ],
            'Celtiis' => [
                [1, 500, 50],
                [501, 5000, 120],
                [5001, 10000, 200],
                [10001, 20000, 300],
                [20001, 50000, 600],
                [50001, 100000, 1000],
                [100001, 200000, 2000],
            ],
            'Ecobank' => [
                [1, 500, 0],
                [501, 5000, 0],
                [5001, 10000, 0],
                [10001, 20000, 0],
                [20001, 50000, 0],
                [50001, 100000, 0],
                [100001, 200000, 0],
            ],
        ];

        foreach (['Moov' => $moov, 'Celtiis' => $celtiis, 'Ecobank' => $ecobank] as $name => $method) {
            foreach ($feeMatrix[$name] as [$min, $max, $fee]) {
                ReceiptFee::create([
                    'method_id' => $method->id,
                    'country_code' => $country->code,
                    'min_amount' => $min,
                    'max_amount' => $max,
                    'fee_amount' => $fee,
                    'fee_type' => 'fixed',
                ]);
            }
        }
    }

    public function test_optimizer_returns_up_to_12_ranked_results_for_175000_fcfa(): void
    {
        $optimizer = new FeeOptimizer();
        $methods = Method::where('country_code', 'BJ')->pluck('id')->toArray();

        $result = $optimizer->optimizeWithdrawal(175000, $methods);

        $this->assertArrayHasKey('best', $result);
        $this->assertArrayHasKey('alternatives', $result);
        $this->assertLessThanOrEqual(12, count([$result['best'], ...$result['alternatives']]));
        $this->assertSame(175000, $result['amount']);
        $this->assertNotEmpty($result['best']['label']);
        $this->assertTrue(empty($result['alternatives']) || $result['best']['fee'] <= $result['alternatives'][0]['fee']);
    }

    public function test_optimizer_contains_single_split_and_combined_options(): void
    {
        $optimizer = new FeeOptimizer();
        $methods = Method::where('country_code', 'BJ')->pluck('id')->toArray();

        $result = $optimizer->optimizeWithdrawal(175000, $methods);
        $labels = array_map(fn ($option) => $option['label'], [$result['best'], ...$result['alternatives']]);

        $this->assertTrue(collect($labels)->contains(fn ($label) => str_contains($label, 'seul')));
        $this->assertTrue(collect($labels)->contains(fn ($label) => str_contains($label, 'fractionné')));
        $this->assertTrue(collect($labels)->contains(fn ($label) => str_contains($label, '+')));
    }

    public function test_optimizer_filters_to_user_methods_only(): void
    {
        $optimizer = new FeeOptimizer();
        $selected = Method::where('name', 'Moov')->pluck('id')->toArray();

        $result = $optimizer->optimizeWithdrawal(175000, $selected);

        $this->assertCount(1, [$result['best']]);
        $this->assertStringContainsString('Moov', $result['best']['label']);
    }
}
