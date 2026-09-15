<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('method_id')->constrained('methods')->cascadeOnDelete();
            $table->string('country_code', 10);
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('fee_amount', 15, 2);
            $table->enum('fee_type', ['fixed', 'percentage']);
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['method_id', 'country_code']);
            $table->index(['min_amount', 'max_amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_fees');
    }
};
