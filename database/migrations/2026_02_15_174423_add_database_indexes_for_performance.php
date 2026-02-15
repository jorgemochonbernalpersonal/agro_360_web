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
        // ==========================================
        // PLOTS TABLE - Índices críticos
        // ==========================================
        Schema::table('plots', function (Blueprint $table) {
            // Foreign keys (si no existen ya)
            if (!$this->indexExists('plots', 'plots_viticulturist_id_index')) {
                $table->index('viticulturist_id');
            }
            if (!$this->indexExists('plots', 'plots_province_id_index')) {
                $table->index('province_id');
            }
            if (!$this->indexExists('plots', 'plots_municipality_id_index')) {
                $table->index('municipality_id');
            }

            // Campos de búsqueda
            if (!$this->indexExists('plots', 'plots_name_index')) {
                $table->index('name');
            }
            if (!$this->indexExists('plots', 'plots_active_index')) {
                $table->index('active');
            }

            // Índice compuesto para queries frecuentes
            if (!$this->indexExists('plots', 'plots_viticulturist_active_index')) {
                $table->index(['viticulturist_id', 'active'], 'plots_viticulturist_active_index');
            }
        });

        // ==========================================
        // AGRICULTURAL_ACTIVITIES TABLE
        // ==========================================
        Schema::table('agricultural_activities', function (Blueprint $table) {
            // Foreign keys
            if (!$this->indexExists('agricultural_activities', 'agricultural_activities_plot_id_index')) {
                $table->index('plot_id');
            }
            if (!$this->indexExists('agricultural_activities', 'agricultural_activities_campaign_id_index')) {
                $table->index('campaign_id');
            }

            // Campos de búsqueda
            if (!$this->indexExists('agricultural_activities', 'agricultural_activities_activity_type_index')) {
                $table->index('activity_type');
            }
            if (!$this->indexExists('agricultural_activities', 'agricultural_activities_activity_date_index')) {
                $table->index('activity_date');
            }

            // Índices compuestos para queries comunes
            if (!$this->indexExists('agricultural_activities', 'activities_plot_campaign_index')) {
                $table->index(['plot_id', 'campaign_id'], 'activities_plot_campaign_index');
            }
            if (!$this->indexExists('agricultural_activities', 'activities_campaign_type_index')) {
                $table->index(['campaign_id', 'activity_type'], 'activities_campaign_type_index');
            }
            if (!$this->indexExists('agricultural_activities', 'activities_date_type_index')) {
                $table->index(['activity_date', 'activity_type'], 'activities_date_type_index');
            }
        });

        // ==========================================
        // CAMPAIGNS TABLE
        // ==========================================
        Schema::table('campaigns', function (Blueprint $table) {
            if (!$this->indexExists('campaigns', 'campaigns_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('campaigns', 'campaigns_year_index')) {
                $table->index('year');
            }
            // Índice compuesto para búsqueda por usuario y año
            if (!$this->indexExists('campaigns', 'campaigns_user_year_index')) {
                $table->index(['user_id', 'year'], 'campaigns_user_year_index');
            }
        });

        // ==========================================
        // SUBSCRIPTIONS TABLE
        // ==========================================
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!$this->indexExists('subscriptions', 'subscriptions_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('subscriptions', 'subscriptions_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('subscriptions', 'subscriptions_ends_at_index')) {
                $table->index('ends_at');
            }
            // Índice compuesto para suscripciones activas por usuario
            if (!$this->indexExists('subscriptions', 'subscriptions_user_status_index')) {
                $table->index(['user_id', 'status'], 'subscriptions_user_status_index');
            }
        });

        // ==========================================
        // PAYMENTS TABLE
        // ==========================================
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->indexExists('payments', 'payments_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('payments', 'payments_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('payments', 'payments_paypal_order_id_index')) {
                $table->index('paypal_order_id');
            }
        });

        // ==========================================
        // OFFICIAL_REPORTS TABLE
        // ==========================================
        Schema::table('official_reports', function (Blueprint $table) {
            if (!$this->indexExists('official_reports', 'official_reports_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('official_reports', 'official_reports_report_type_index')) {
                $table->index('report_type');
            }
            if (!$this->indexExists('official_reports', 'official_reports_verification_code_index')) {
                $table->index('verification_code');
            }
            if (!$this->indexExists('official_reports', 'official_reports_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ==========================================
        // PHYTOSANITARY_TREATMENTS TABLE
        // ==========================================
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            if (!$this->indexExists('phytosanitary_treatments', 'phytosanitary_treatments_activity_id_index')) {
                $table->index('agricultural_activity_id', 'phytosanitary_treatments_activity_id_index');
            }
            if (!$this->indexExists('phytosanitary_treatments', 'phytosanitary_treatments_product_id_index')) {
                $table->index('product_id');
            }
        });

        // ==========================================
        // INVOICES TABLE
        // ==========================================
        Schema::table('invoices', function (Blueprint $table) {
            if (!$this->indexExists('invoices', 'invoices_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('invoices', 'invoices_client_id_index')) {
                $table->index('client_id');
            }
            if (!$this->indexExists('invoices', 'invoices_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('invoices', 'invoices_invoice_date_index')) {
                $table->index('invoice_date');
            }
            if (!$this->indexExists('invoices', 'invoices_invoice_number_index')) {
                $table->index('invoice_number');
            }
        });

        // ==========================================
        // USERS TABLE
        // ==========================================
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_email_index')) {
                $table->index('email');
            }
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices en orden inverso
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['invoice_number']);
        });

        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->dropIndex('phytosanitary_treatments_activity_id_index');
            $table->dropIndex(['product_id']);
        });

        Schema::table('official_reports', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['report_type']);
            $table->dropIndex(['verification_code']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['paypal_order_id']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['ends_at']);
            $table->dropIndex('subscriptions_user_status_index');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['year']);
            $table->dropIndex('campaigns_user_year_index');
        });

        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropIndex(['plot_id']);
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['activity_type']);
            $table->dropIndex(['activity_date']);
            $table->dropIndex('activities_plot_campaign_index');
            $table->dropIndex('activities_campaign_type_index');
            $table->dropIndex('activities_date_type_index');
        });

        Schema::table('plots', function (Blueprint $table) {
            $table->dropIndex(['viticulturist_id']);
            $table->dropIndex(['province_id']);
            $table->dropIndex(['municipality_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['active']);
            $table->dropIndex('plots_viticulturist_active_index');
        });
    }

    /**
     * Check if index exists
     */
    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($table);
        
        return isset($indexes[$index]);
    }
};
