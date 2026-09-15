<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            $table->decimal('caisse_physique', 15, 2)->default(0)->after('city');
            $table->decimal('caisse_virtuelle', 15, 2)->default(0)->after('caisse_physique');
            $table->date('caisse_date')->nullable()->after('caisse_virtuelle');
        });
    }

    public function down(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            $table->dropColumn(['caisse_physique', 'caisse_virtuelle', 'caisse_date']);
        });
    }
};