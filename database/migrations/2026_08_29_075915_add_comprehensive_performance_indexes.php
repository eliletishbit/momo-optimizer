<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute tous les index nécessaires pour optimiser les performances
     */
    public function up(): void
    {
        // =============================================
        // 1. TABLE users
        // =============================================
        Schema::table('users', function (Blueprint $table) {
            // Index existants déjà présents : country_code, subscription
            // Ajout des index manquants
            if (!Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }
            if (!Schema::hasIndex('users', 'users_subscription_expires_at_index')) {
                $table->index('subscription_expires_at');
            }
            if (!Schema::hasIndex('users', 'users_is_admin_index')) {
                $table->index('is_admin');
            }
            if (!Schema::hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
            // Index composite pour les recherches fréquentes
            if (!Schema::hasIndex('users', 'users_subscription_is_admin_index')) {
                $table->index(['subscription', 'is_admin']);
            }
            if (!Schema::hasIndex('users', 'users_country_subscription_index')) {
                $table->index(['country_code', 'subscription']);
            }
        });

        // =============================================
        // 2. TABLE countries
        // =============================================
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasIndex('countries', 'countries_is_active_index')) {
                $table->index('is_active');
            }
            if (!Schema::hasIndex('countries', 'countries_code_is_active_index')) {
                $table->index(['code', 'is_active']);
            }
        });

        // =============================================
        // 3. TABLE methods
        // =============================================
        Schema::table('methods', function (Blueprint $table) {
            // Index existants : country_code, category
            if (!Schema::hasIndex('methods', 'methods_code_index')) {
                $table->index('code');
            }
            if (!Schema::hasIndex('methods', 'methods_is_active_index')) {
                $table->index('is_active');
            }
            if (!Schema::hasIndex('methods', 'methods_country_code_is_active_index')) {
                $table->index(['country_code', 'is_active']);
            }
            if (!Schema::hasIndex('methods', 'methods_category_is_active_index')) {
                $table->index(['category', 'is_active']);
            }
            // Index composite pour les recherches principales
            if (!Schema::hasIndex('methods', 'methods_country_category_active_index')) {
                $table->index(['country_code', 'category', 'is_active']);
            }
        });

        // =============================================
        // 4. TABLE user_methods
        // =============================================
        Schema::table('user_methods', function (Blueprint $table) {
            // Index existants : user_id, type, method_id
            // Ajout des index composites
            if (!Schema::hasIndex('user_methods', 'user_methods_user_id_method_id_index')) {
                $table->index(['user_id', 'method_id']);
            }
            if (!Schema::hasIndex('user_methods', 'user_methods_user_id_type_index')) {
                $table->index(['user_id', 'type']);
            }
            if (!Schema::hasIndex('user_methods', 'user_methods_user_id_is_default_index')) {
                $table->index(['user_id', 'is_default']);
            }
            if (!Schema::hasIndex('user_methods', 'user_methods_method_id_type_index')) {
                $table->index(['method_id', 'type']);
            }
            // Index composite complet pour les requêtes de calcul
            if (!Schema::hasIndex('user_methods', 'user_methods_user_type_default_index')) {
                $table->index(['user_id', 'type', 'is_default']);
            }
        });

        // =============================================
        // 5. TABLE receipt_fees
        // =============================================
        Schema::table('receipt_fees', function (Blueprint $table) {
            // Index existants : method_id, country_code, min_amount, max_amount
            if (!Schema::hasIndex('receipt_fees', 'receipt_fees_method_id_country_code_index')) {
                $table->index(['method_id', 'country_code']);
            }
            if (!Schema::hasIndex('receipt_fees', 'receipt_fees_min_amount_max_amount_index')) {
                $table->index(['min_amount', 'max_amount']);
            }
            if (!Schema::hasIndex('receipt_fees', 'receipt_fees_country_code_index')) {
                $table->index('country_code');
            }
            // Index pour les recherches de frais
            if (!Schema::hasIndex('receipt_fees', 'receipt_fees_method_country_amount_index')) {
                $table->index(['method_id', 'country_code', 'min_amount', 'max_amount']);
            }
            if (!Schema::hasIndex('receipt_fees', 'receipt_fees_method_fee_type_index')) {
                $table->index(['method_id', 'fee_type']);
            }
        });

        // =============================================
        // 6. TABLE sending_fees
        // =============================================
        Schema::table('sending_fees', function (Blueprint $table) {
            // Index existants : method_id, country_code, min_amount, max_amount
            if (!Schema::hasIndex('sending_fees', 'sending_fees_method_id_country_code_index')) {
                $table->index(['method_id', 'country_code']);
            }
            if (!Schema::hasIndex('sending_fees', 'sending_fees_min_amount_max_amount_index')) {
                $table->index(['min_amount', 'max_amount']);
            }
            if (!Schema::hasIndex('sending_fees', 'sending_fees_country_code_index')) {
                $table->index('country_code');
            }
            // Index pour les recherches de frais
            if (!Schema::hasIndex('sending_fees', 'sending_fees_method_country_amount_index')) {
                $table->index(['method_id', 'country_code', 'min_amount', 'max_amount']);
            }
            if (!Schema::hasIndex('sending_fees', 'sending_fees_method_fee_type_index')) {
                $table->index(['method_id', 'fee_type']);
            }
        });

        // =============================================
        // 7. TABLE subscriptions
        // =============================================
        Schema::table('subscriptions', function (Blueprint $table) {
            // Index existants : user_id, status, end_date
            if (!Schema::hasIndex('subscriptions', 'subscriptions_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!Schema::hasIndex('subscriptions', 'subscriptions_status_end_date_index')) {
                $table->index(['status', 'end_date']);
            }
            if (!Schema::hasIndex('subscriptions', 'subscriptions_plan_status_index')) {
                $table->index(['plan', 'status']);
            }
            if (!Schema::hasIndex('subscriptions', 'subscriptions_user_id_plan_index')) {
                $table->index(['user_id', 'plan']);
            }
            // Index pour les abonnements actifs
            if (!Schema::hasIndex('subscriptions', 'subscriptions_user_status_end_date_index')) {
                $table->index(['user_id', 'status', 'end_date']);
            }
        });

        // =============================================
        // 8. TABLE optimization_history
        // =============================================
        Schema::table('optimization_history', function (Blueprint $table) {
            // Index existants : user_id, created_at
            if (!Schema::hasIndex('optimization_history', 'optimization_history_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if (!Schema::hasIndex('optimization_history', 'optimization_history_user_id_type_index')) {
                $table->index(['user_id', 'type']);
            }
            if (!Schema::hasIndex('optimization_history', 'optimization_history_created_at_index')) {
                $table->index('created_at');
            }
            if (!Schema::hasIndex('optimization_history', 'optimization_history_user_type_created_index')) {
                $table->index(['user_id', 'type', 'created_at']);
            }
        });

        // =============================================
        // 9. ANALYZER les tables pour mettre à jour les statistiques
        // =============================================
        DB::statement('ANALYZE users');
        DB::statement('ANALYZE countries');
        DB::statement('ANALYZE methods');
        DB::statement('ANALYZE user_methods');
        DB::statement('ANALYZE receipt_fees');
        DB::statement('ANALYZE sending_fees');
        DB::statement('ANALYZE subscriptions');
        DB::statement('ANALYZE optimization_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index (optionnel, mais recommandé pour le rollback)
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_email_index');
            $table->dropIndexIfExists('users_subscription_expires_at_index');
            $table->dropIndexIfExists('users_is_admin_index');
            $table->dropIndexIfExists('users_created_at_index');
            $table->dropIndexIfExists('users_subscription_is_admin_index');
            $table->dropIndexIfExists('users_country_subscription_index');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndexIfExists('countries_is_active_index');
            $table->dropIndexIfExists('countries_code_is_active_index');
        });

        Schema::table('methods', function (Blueprint $table) {
            $table->dropIndexIfExists('methods_code_index');
            $table->dropIndexIfExists('methods_is_active_index');
            $table->dropIndexIfExists('methods_country_code_is_active_index');
            $table->dropIndexIfExists('methods_category_is_active_index');
            $table->dropIndexIfExists('methods_country_category_active_index');
        });

        Schema::table('user_methods', function (Blueprint $table) {
            $table->dropIndexIfExists('user_methods_user_id_method_id_index');
            $table->dropIndexIfExists('user_methods_user_id_type_index');
            $table->dropIndexIfExists('user_methods_user_id_is_default_index');
            $table->dropIndexIfExists('user_methods_method_id_type_index');
            $table->dropIndexIfExists('user_methods_user_type_default_index');
        });

        Schema::table('receipt_fees', function (Blueprint $table) {
            $table->dropIndexIfExists('receipt_fees_method_id_country_code_index');
            $table->dropIndexIfExists('receipt_fees_min_amount_max_amount_index');
            $table->dropIndexIfExists('receipt_fees_country_code_index');
            $table->dropIndexIfExists('receipt_fees_method_country_amount_index');
            $table->dropIndexIfExists('receipt_fees_method_fee_type_index');
        });

        Schema::table('sending_fees', function (Blueprint $table) {
            $table->dropIndexIfExists('sending_fees_method_id_country_code_index');
            $table->dropIndexIfExists('sending_fees_min_amount_max_amount_index');
            $table->dropIndexIfExists('sending_fees_country_code_index');
            $table->dropIndexIfExists('sending_fees_method_country_amount_index');
            $table->dropIndexIfExists('sending_fees_method_fee_type_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndexIfExists('subscriptions_user_id_status_index');
            $table->dropIndexIfExists('subscriptions_status_end_date_index');
            $table->dropIndexIfExists('subscriptions_plan_status_index');
            $table->dropIndexIfExists('subscriptions_user_id_plan_index');
            $table->dropIndexIfExists('subscriptions_user_status_end_date_index');
        });

        Schema::table('optimization_history', function (Blueprint $table) {
            $table->dropIndexIfExists('optimization_history_user_id_created_at_index');
            $table->dropIndexIfExists('optimization_history_user_id_type_index');
            $table->dropIndexIfExists('optimization_history_created_at_index');
            $table->dropIndexIfExists('optimization_history_user_type_created_index');
        });
    }
};