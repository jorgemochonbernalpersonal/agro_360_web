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
        // Índices para tabla pivot plot_sigpac_use (si existe)
        if (Schema::hasTable('plot_sigpac_use')) {
            Schema::table('plot_sigpac_use', function (Blueprint $table) {
                $table->index('plot_id', 'plot_sigpac_use_plot_idx');
                $table->index('sigpac_use_id', 'plot_sigpac_use_use_idx');
            });
        }

        // Índices para tabla pivot plot_sigpac_code (si existe)
        if (Schema::hasTable('plot_sigpac_code')) {
            Schema::table('plot_sigpac_code', function (Blueprint $table) {
                $table->index('plot_id', 'plot_sigpac_code_plot_idx');
                $table->index('sigpac_code_id', 'plot_sigpac_code_code_idx');
            });
        }

        // Índices para relación winery_viticulturist
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->index('winery_id', 'wv_winery_idx');
            $table->index('viticulturist_id', 'wv_viticulturist_idx');
            $table->index('source', 'wv_source_idx');
            $table->index('parent_viticulturist_id', 'wv_parent_viticulturist_idx');
            $table->index('supervisor_id', 'wv_supervisor_idx');
        });

        // Índices para viticulturist_hierarchy (si existe)
        if (Schema::hasTable('viticulturist_hierarchy')) {
            Schema::table('viticulturist_hierarchy', function (Blueprint $table) {
                $table->index('parent_viticulturist_id', 'vh_parent_idx');
                $table->index('child_viticulturist_id', 'vh_child_idx');
                $table->index('winery_id', 'vh_winery_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plot_sigpac_use')) {
            Schema::table('plot_sigpac_use', function (Blueprint $table) {
                $table->dropIndex('plot_sigpac_use_plot_idx');
                $table->dropIndex('plot_sigpac_use_use_idx');
            });
        }

        if (Schema::hasTable('plot_sigpac_code')) {
            Schema::table('plot_sigpac_code', function (Blueprint $table) {
                $table->dropIndex('plot_sigpac_code_plot_idx');
                $table->dropIndex('plot_sigpac_code_code_idx');
            });
        }

        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->dropIndex('wv_winery_idx');
            $table->dropIndex('wv_viticulturist_idx');
            $table->dropIndex('wv_source_idx');
            $table->dropIndex('wv_parent_viticulturist_idx');
            $table->dropIndex('wv_supervisor_idx');
        });

        if (Schema::hasTable('viticulturist_hierarchy')) {
            Schema::table('viticulturist_hierarchy', function (Blueprint $table) {
                $table->dropIndex('vh_parent_idx');
                $table->dropIndex('vh_child_idx');
                $table->dropIndex('vh_winery_idx');
            });
        }
    }
};
