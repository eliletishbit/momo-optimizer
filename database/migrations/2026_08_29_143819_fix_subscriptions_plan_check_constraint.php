<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Supprimer l'ancienne contrainte
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_plan_check');
        
        // ✅ Créer la nouvelle contrainte
        DB::statement("
            ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_plan_check 
            CHECK (plan IN ('premium', 'pro', 'business', 'pay_as_you_go'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_plan_check');
        
        // ✅ Revenir à l'ancienne contrainte
        DB::statement("
            ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_plan_check 
            CHECK (plan IN ('premium', 'pro', 'business'))
        ");
    }
};