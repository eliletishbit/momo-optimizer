<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->enum('category', ['mobile_money', 'bank', 'international', 'crypto', 'other']);
            $table->string('country_code', 10)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('color_primary', 7)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_code', 'category']);
            $table->foreign('country_code')->references('code')->on('countries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('methods');
    }
};
