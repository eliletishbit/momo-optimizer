<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_operation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom', 50)->unique(); // Retrait, Envoi, Recharge, etc.
            $table->string('code', 30)->unique(); // withdrawal, sending, airtime, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_operation');
    }
};