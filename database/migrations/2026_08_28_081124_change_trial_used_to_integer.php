<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ 1. Supprimer d'abord la valeur par défaut
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used DROP DEFAULT');
        
        // ✅ 2. Convertir la colonne en INTEGER
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used TYPE INTEGER USING CASE WHEN trial_used THEN 1 ELSE 0 END');
        
        // ✅ 3. Remettre une valeur par défaut
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used SET DEFAULT 0');
    }

    public function down(): void
    {
        // ✅ Revenir en arrière
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used DROP DEFAULT');
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used TYPE BOOLEAN USING CASE WHEN trial_used = 0 THEN false ELSE true END');
        DB::statement('ALTER TABLE users ALTER COLUMN trial_used SET DEFAULT false');
    }
};