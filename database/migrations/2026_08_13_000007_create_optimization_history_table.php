<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['receipt', 'send']);
            $table->decimal('amount', 15, 2);
            $table->foreignUuid('selected_method_id')->constrained('methods')->cascadeOnDelete();
            $table->decimal('total_fee', 15, 2);
            $table->decimal('savings', 15, 2);
            $table->json('alternatives')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimization_history');
    }
};
