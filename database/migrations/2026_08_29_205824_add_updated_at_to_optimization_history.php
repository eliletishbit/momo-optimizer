<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('optimization_history', function (Blueprint $table) {
            // ✅ Ajouter updated_at (nullable pour les anciennes entrées)
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('optimization_history', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};