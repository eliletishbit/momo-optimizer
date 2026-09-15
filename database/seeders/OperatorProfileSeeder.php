<?php

namespace Database\Seeders;

use App\Models\OperatorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorProfileSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Récupérer l'utilisateur rodrigue
        $user = User::where('email', 'rodrigueapothey@gmail.com')->first();

        if (!$user) {
            $this->command->warn('⚠️ Utilisateur non trouvé');
            return;
        }

        // ✅ Vérifier si un profil existe déjà
        $existing = OperatorProfile::whereRaw('user_id = ?', [$user->id])->first();
        
        if ($existing) {
            $this->command->warn('⚠️ Un profil opérateur existe déjà pour ' . $user->email);
            return;
        }

        // ✅ Créer le profil avec DB::raw pour PostgreSQL
        OperatorProfile::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'business_name' => 'MomoOpti Pro',
            'phone' => '90123456',
            'address' => 'Cotonou, Bénin',
            'city' => 'Cotonou',
            // ✅ Utiliser DB::raw('true') pour PostgreSQL
            'is_active' => DB::raw('true'),
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);

        $this->command->info('✅ Profil opérateur créé pour ' . $user->email);
    }
}