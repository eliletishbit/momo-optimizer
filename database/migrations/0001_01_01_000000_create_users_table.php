<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('country_code', 10)->default('BJ');
            $table->json('preferred_receipt_methods')->nullable();
            $table->json('preferred_sending_methods')->nullable();
            $table->enum('subscription', ['free', 'premium', 'pro', 'business'])->default('free');
            $table->boolean('is_admin')->default(false);
            $table->timestamp('subscription_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('subscription');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
