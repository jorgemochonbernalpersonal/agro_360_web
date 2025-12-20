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
        // Función helper para verificar si el índice existe
        $indexExists = function($table, $index) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);
                return !empty($result);
            }
            // Para MySQL/MariaDB
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return !empty($result);
        };

        // Índices para la tabla plots
        // Nota: winery_id fue eliminado de plots en una migración posterior (2025_12_18_101200)
        Schema::table('plots', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('plots', 'idx_plots_viticulturist')) {
                $table->index('viticulturist_id', 'idx_plots_viticulturist');
            }
            if (!$indexExists('plots', 'idx_plots_active')) {
                $table->index('active', 'idx_plots_active');
            }
            if (!$indexExists('plots', 'idx_plots_name')) {
                $table->index('name', 'idx_plots_name');
            }
            if (!$indexExists('plots', 'idx_plots_municipality')) {
                $table->index('municipality_id', 'idx_plots_municipality');
            }
        });

        // Índices para users
        Schema::table('users', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('users', 'idx_users_role')) {
                $table->index('role', 'idx_users_role');
            }
            if (!$indexExists('users', 'idx_users_role_email')) {
                $table->index(['role', 'email'], 'idx_users_role_email');
            }
        });

        // Índices para winery_viticulturist
        Schema::table('winery_viticulturist', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('winery_viticulturist', 'idx_wv_winery')) {
                $table->index('winery_id', 'idx_wv_winery');
            }
            if (!$indexExists('winery_viticulturist', 'idx_wv_viticulturist')) {
                $table->index('viticulturist_id', 'idx_wv_viticulturist');
            }
            if (!$indexExists('winery_viticulturist', 'idx_wv_supervisor')) {
                $table->index('supervisor_id', 'idx_wv_supervisor');
            }
            if (!$indexExists('winery_viticulturist', 'idx_wv_parent')) {
                $table->index('parent_viticulturist_id', 'idx_wv_parent');
            }
            if (!$indexExists('winery_viticulturist', 'idx_wv_source')) {
                $table->index('source', 'idx_wv_source');
            }
            if (!$indexExists('winery_viticulturist', 'idx_wv_winery_source')) {
                $table->index(['winery_id', 'source'], 'idx_wv_winery_source');
            }
        });

        // Índices para agricultural_activities (si la tabla existe)
        if (Schema::hasTable('agricultural_activities')) {
            Schema::table('agricultural_activities', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('agricultural_activities', 'idx_aa_plot')) {
                    $table->index('plot_id', 'idx_aa_plot');
                }
                if (!$indexExists('agricultural_activities', 'idx_aa_type')) {
                    $table->index('activity_type', 'idx_aa_type');
                }
                if (!$indexExists('agricultural_activities', 'idx_aa_date')) {
                    $table->index('activity_date', 'idx_aa_date');
                }
                if (!$indexExists('agricultural_activities', 'idx_aa_plot_date')) {
                    $table->index(['plot_id', 'activity_date'], 'idx_aa_plot_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropIndex('idx_plots_viticulturist');
            $table->dropIndex('idx_plots_active');
            $table->dropIndex('idx_plots_name');
            $table->dropIndex('idx_plots_municipality');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_role_email');
        });

        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->dropIndex('idx_wv_winery');
            $table->dropIndex('idx_wv_viticulturist');
            $table->dropIndex('idx_wv_supervisor');
            $table->dropIndex('idx_wv_parent');
            $table->dropIndex('idx_wv_source');
            $table->dropIndex('idx_wv_winery_source');
        });

        if (Schema::hasTable('agricultural_activities')) {
            Schema::table('agricultural_activities', function (Blueprint $table) {
                $table->dropIndex('idx_aa_plot');
                $table->dropIndex('idx_aa_type');
                $table->dropIndex('idx_aa_date');
                $table->dropIndex('idx_aa_plot_date');
            });
        }
    }
};
