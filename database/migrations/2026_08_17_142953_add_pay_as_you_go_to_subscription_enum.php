<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        // Pour PostgreSQL, on modifie le type ENUM
        DB::statement("ALTER TABLE users ALTER COLUMN subscription TYPE VARCHAR(30)");
        DB::statement("ALTER TABLE users ALTER COLUMN subscription SET DEFAULT 'free'");
        
        // Optionnel : recréer l'ENUM si tu veux garder une contrainte
        // Mais avec VARCHAR, plus besoin d'ENUM.
    }

    public function down(): void
    {
        // On peut revenir en arrière si besoin
        DB::statement("ALTER TABLE users ALTER COLUMN subscription TYPE VARCHAR(30)");
    }
};
