<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            // FK al Harvest de bodega que confirmó esta entrega (nullable = aún sin confirmar)
            $table->foreignId('harvest_id')
                ->nullable()
                ->after('plot_planting_id')
                ->constrained('harvests')
                ->nullOnDelete();

            // Estado del ciclo de vida de la entrega declarada
            $table->enum('status', ['pending', 'matched', 'disputed'])
                ->default('pending')
                ->after('harvest_id');

            // Diferencia en kg entre lo declarado y lo registrado por la bodega
            $table->decimal('discrepancy_kg', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->dropForeign(['harvest_id']);
            $table->dropColumn(['harvest_id', 'status', 'discrepancy_kg']);
        });
    }
};
