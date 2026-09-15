<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_virtual_subaccounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('operator_profile_id')
                ->constrained('operator_profiles')
                ->cascadeOnDelete();
            $table->string('reseau', 50); // MTN, Moov, etc.
            $table->decimal('solde', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['operator_profile_id', 'reseau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_virtual_subaccounts');
    }
};