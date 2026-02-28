<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrar registros phytosanitary → other antes de modificar el ENUM
        DB::statement("UPDATE supplies SET supply_type = 'other' WHERE supply_type = 'phytosanitary'");

        // 2. Modificar ENUM eliminando 'phytosanitary'
        DB::statement("ALTER TABLE supplies MODIFY COLUMN supply_type ENUM('fertilizer','seed','postharvest','other') NOT NULL DEFAULT 'other'");

        // 3. Añadir warehouse_id
        Schema::table('supplies', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('viticulturist_id')
                ->constrained('warehouses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Warehouse::class);
            $table->dropColumn('warehouse_id');
        });

        DB::statement("ALTER TABLE supplies MODIFY COLUMN supply_type ENUM('phytosanitary','fertilizer','seed','postharvest','other') NOT NULL DEFAULT 'phytosanitary'");
    }
};
