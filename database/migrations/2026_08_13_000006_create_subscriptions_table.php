<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('plan', ['premium', 'pro', 'business']);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
