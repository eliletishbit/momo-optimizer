<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $receiptGrid = [
            'mtn_bj' => [
                ['min_amount' => 1, 'max_amount' => 500, 'fee_amount' => 50, 'fee_type' => 'fixed'],
                ['min_amount' => 501, 'max_amount' => 5000, 'fee_amount' => 125, 'fee_type' => 'fixed'],
                ['min_amount' => 5001, 'max_amount' => 10000, 'fee_amount' => 225, 'fee_type' => 'fixed'],
                ['min_amount' => 10001, 'max_amount' => 20000, 'fee_amount' => 375, 'fee_type' => 'fixed'],
                ['min_amount' => 20001, 'max_amount' => 50000, 'fee_amount' => 700, 'fee_type' => 'fixed'],
                ['min_amount' => 50001, 'max_amount' => 100000, 'fee_amount' => 1000, 'fee_type' => 'fixed'],
                ['min_amount' => 100001, 'max_amount' => 200000, 'fee_amount' => 2000, 'fee_type' => 'fixed'],
            ],
            'moov_bj' => [
                ['min_amount' => 1, 'max_amount' => 500, 'fee_amount' => 45, 'fee_type' => 'fixed'],
                ['min_amount' => 501, 'max_amount' => 5000, 'fee_amount' => 110, 'fee_type' => 'fixed'],
                ['min_amount' => 5001, 'max_amount' => 10000, 'fee_amount' => 200, 'fee_type' => 'fixed'],
            ],
            'celtiis_bj' => [
                ['min_amount' => 1, 'max_amount' => 10000, 'fee_amount' => 1.5, 'fee_type' => 'percentage'],
                ['min_amount' => 10001, 'max_amount' => 50000, 'fee_amount' => 2.0, 'fee_type' => 'percentage'],
            ],
        ];

        $sendingGrid = [
            'mtn_bj' => [
                ['min_amount' => 1, 'max_amount' => 500, 'fee_amount' => 50, 'fee_type' => 'fixed'],
                ['min_amount' => 501, 'max_amount' => 5000, 'fee_amount' => 150, 'fee_type' => 'fixed'],
                ['min_amount' => 5001, 'max_amount' => 10000, 'fee_amount' => 250, 'fee_type' => 'fixed'],
            ],
            'moov_bj' => [
                ['min_amount' => 1, 'max_amount' => 500, 'fee_amount' => 40, 'fee_type' => 'fixed'],
                ['min_amount' => 501, 'max_amount' => 5000, 'fee_amount' => 120, 'fee_type' => 'fixed'],
            ],
            'celtiis_bj' => [
                ['min_amount' => 1, 'max_amount' => 10000, 'fee_amount' => 1.2, 'fee_type' => 'percentage'],
                ['min_amount' => 10001, 'max_amount' => 50000, 'fee_amount' => 1.7, 'fee_type' => 'percentage'],
            ],
        ];

        foreach ($receiptGrid as $code => $ranges) {
            $method = Method::where('code', $code)->first();
            if (! $method) {
                continue;
            }

            foreach ($ranges as $range) {
                DB::table('receipt_fees')->updateOrInsert(
                    [
                        'method_id' => $method->id,
                        'country_code' => $method->country_code,
                        'min_amount' => $range['min_amount'],
                        'max_amount' => $range['max_amount'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'fee_amount' => $range['fee_amount'],
                        'fee_type' => $range['fee_type'],
                    ]
                );
            }
        }

        foreach ($sendingGrid as $code => $ranges) {
            $method = Method::where('code', $code)->first();
            if (! $method) {
                continue;
            }

            foreach ($ranges as $range) {
                DB::table('sending_fees')->updateOrInsert(
                    [
                        'method_id' => $method->id,
                        'country_code' => $method->country_code,
                        'min_amount' => $range['min_amount'],
                        'max_amount' => $range['max_amount'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'fee_amount' => $range['fee_amount'],
                        'fee_type' => $range['fee_type'],
                    ]
                );
            }
        }
    }
}
