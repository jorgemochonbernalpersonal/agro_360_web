<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deshabilitar foreign key checks temporalmente (solo MySQL/MariaDB)
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // PLOTS TABLE
        $this->addIndexes('plots', [
            'viticulturist_id',
            'province_id',
            'municipality_id',
            'name',
            'active',
        ]);
        $this->addCompositeIndex('plots', ['viticulturist_id', 'active'], 'plots_viticulturist_active_idx');

        // AGRICULTURAL_ACTIVITIES TABLE
        $this->addIndexes('agricultural_activities', [
            'plot_id',
            'campaign_id',
            'activity_type',
            'activity_date',
        ]);
        $this->addCompositeIndex('agricultural_activities', ['plot_id', 'campaign_id'], 'activities_plot_campaign_idx');
        $this->addCompositeIndex('agricultural_activities', ['campaign_id', 'activity_type'], 'activities_campaign_type_idx');
        $this->addCompositeIndex('agricultural_activities', ['activity_date', 'activity_type'], 'activities_date_type_idx');

        // CAMPAIGNS TABLE
        $this->addIndexes('campaigns', ['viticulturist_id', 'year']);
        $this->addCompositeIndex('campaigns', ['viticulturist_id', 'year'], 'campaigns_user_year_idx');

        // SUBSCRIPTIONS TABLE
        $this->addIndexes('subscriptions', ['user_id', 'status', 'ends_at']);
        $this->addCompositeIndex('subscriptions', ['user_id', 'status'], 'subscriptions_user_status_idx');

        // PAYMENTS TABLE
        $this->addIndexes('payments', ['user_id', 'status', 'paypal_order_id']);

        // OFFICIAL_REPORTS TABLE
        $this->addIndexes('official_reports', ['user_id', 'report_type', 'verification_code', 'created_at']);

        // PHYTOSANITARY_TREATMENTS TABLE
        $this->addIndexes('phytosanitary_treatments', ['agricultural_activity_id', 'product_id']);

        // INVOICES TABLE
        $this->addIndexes('invoices', ['user_id', 'client_id', 'status', 'invoice_date', 'invoice_number']);

        // USERS TABLE
        $this->addIndexes('users', ['email', 'role']);

        // Rehabilitar foreign key checks
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminamos índices en rollback por seguridad
    }

    /**
     * Add multiple indexes to a table
     */
    protected function addIndexes(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            $indexName = "{$table}_{$column}_index";
            
            try {
                DB::statement("CREATE INDEX {$indexName} ON {$table}({$column})");
            } catch (\Exception $e) {
                // Index already exists - ignore
            }
        }
    }

    /**
     * Add composite index to a table
     */
    protected function addCompositeIndex(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columnsList = implode(', ', $columns);
        
        try {
            DB::statement("CREATE INDEX {$indexName} ON {$table}({$columnsList})");
        } catch (\Exception $e) {
            // Index already exists - ignore
        }
    }
};
