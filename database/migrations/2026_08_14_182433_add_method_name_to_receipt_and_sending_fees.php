<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajout de la colonne method_name dans receipt_fees
        Schema::table('receipt_fees', function (Blueprint $table) {
            $table->string('method_name', 100)->nullable()->after('method_id');
        });

        // Ajout de la colonne method_name dans sending_fees
        Schema::table('sending_fees', function (Blueprint $table) {
            $table->string('method_name', 100)->nullable()->after('method_id');
        });

        // Remplir les colonnes avec les noms des méthodes existantes
        $this->fillMethodNames('receipt_fees');
        $this->fillMethodNames('sending_fees');

        // Rendre la colonne method_name non nullable après remplissage
        Schema::table('receipt_fees', function (Blueprint $table) {
            $table->string('method_name', 100)->nullable(false)->change();
        });

        Schema::table('sending_fees', function (Blueprint $table) {
            $table->string('method_name', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_fees', function (Blueprint $table) {
            $table->dropColumn('method_name');
        });

        Schema::table('sending_fees', function (Blueprint $table) {
            $table->dropColumn('method_name');
        });
    }

    /**
     * Remplit la colonne method_name en joignant la table methods.
     */
    private function fillMethodNames(string $tableName): void
    {
        $table = DB::table($tableName);
        $rows = $table->get();

        foreach ($rows as $row) {
            // Récupérer le nom de la méthode via l'ID
            $method = DB::table('methods')->where('id', $row->method_id)->first();
            if ($method) {
                $table->where('id', $row->id)->update(['method_name' => $method->name]);
            }
        }
    }
};