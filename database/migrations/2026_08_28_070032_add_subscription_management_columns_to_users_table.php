<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ Colonne pour savoir si l'essai gratuit a déjà été utilisé
            $table->boolean('trial_used')->default(false)->after('subscription_expires_at');
            
            // ✅ Colonnes pour le mode dégradé (1 calcul/mois)
            $table->timestamp('last_calculator_use_at')->nullable()->after('trial_used');
            $table->integer('calculator_use_count_month')->default(0)->after('last_calculator_use_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trial_used',
                'last_calculator_use_at',
                'calculator_use_count_month'
            ]);
        });
    }
};