<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->unique(); // ✅ Un seul profil par utilisateur
            
            // ✅ Informations de l'opérateur
            $table->string('business_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 50)->nullable();
            
            // ✅ Statut de l'opérateur
            $table->boolean('is_active')->default(true);
            
            // ✅ Qui a approuvé et quand
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->timestamps();
            
            // ✅ Index pour les recherches
            $table->index(['is_active', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_profiles');
    }
};