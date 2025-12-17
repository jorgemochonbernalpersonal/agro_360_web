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
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Índices individuales para queries frecuentes
            $table->index('parent_viticulturist_id', 'idx_winery_viticulturist_parent');
            $table->index('source', 'idx_winery_viticulturist_source');
            $table->index('assigned_by', 'idx_winery_viticulturist_assigned_by');
            $table->index('supervisor_id', 'idx_winery_viticulturist_supervisor');
            
            // Índice compuesto para wasCreatedByAnotherUser() y visibleTo()
            $table->index(['viticulturist_id', 'source', 'assigned_by'], 'idx_winery_viticulturist_created');
            
            // Índice compuesto para queries de visibilidad
            $table->index(['viticulturist_id', 'winery_id', 'source'], 'idx_winery_viticulturist_visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->dropIndex('idx_winery_viticulturist_parent');
            $table->dropIndex('idx_winery_viticulturist_source');
            $table->dropIndex('idx_winery_viticulturist_assigned_by');
            $table->dropIndex('idx_winery_viticulturist_supervisor');
            $table->dropIndex('idx_winery_viticulturist_created');
            $table->dropIndex('idx_winery_viticulturist_visibility');
        });
    }
};

