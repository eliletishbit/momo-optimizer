<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('method_id')->constrained('methods')->cascadeOnDelete();
            $table->enum('type', ['receipt', 'send', 'both']);
            $table->string('account_id', 255)->nullable();
            $table->string('account_label', 100)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'method_id', 'type']);
            $table->index(['user_id', 'type']);
            $table->index('method_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_methods');
    }
};
