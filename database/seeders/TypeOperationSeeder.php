<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeOperation;

class TypeOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        $types = [
            ['nom' => 'Retrait', 'code' => 'withdrawal'],
            ['nom' => 'Envoi', 'code' => 'sending'],
            ['nom' => 'Recharge', 'code' => 'airtime'],
            ['nom' => 'Forfait Internet', 'code' => 'internet'],
            ['nom' => 'Paiement de facture', 'code' => 'bill'],
            ['nom' => 'Dépôt', 'code' => 'deposit'],
            ['nom' => 'Autre', 'code' => 'other'],
        ];

        foreach ($types as $type) {
            TypeOperation::create($type);
        }
    }
}
