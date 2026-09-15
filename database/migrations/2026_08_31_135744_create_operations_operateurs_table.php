<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations_operateurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            
            $table->foreignUuid('type_operation_id')
                ->constrained('types_operation')
                ->cascadeOnDelete();
            
            $table->string('reseau', 50); // MTN, Moov, Celtiis, Orange
            $table->string('telephone_client', 20)->nullable();
            $table->decimal('montant', 15, 2);
            
            // ✅ Direction : entrant ou sortant
            $table->enum('direction', ['entrant', 'sortant']);
            
            $table->timestamps();
            
            // ✅ Index pour les recherches fréquentes
            $table->index(['user_id', 'created_at']);
            $table->index(['reseau']);
            $table->index(['direction']);
            $table->index(['telephone_client']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_operateurs');
    }
};