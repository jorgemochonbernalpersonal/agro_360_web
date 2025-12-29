<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar campos de stock para consolidar con container_states
     */
    public function up(): void
    {
        Schema::table('container_current_states', function (Blueprint $table) {
            $table->decimal('available_qty', 10, 3)->default(0.000)->after('current_quantity')->comment('Cantidad disponible');
            $table->decimal('reserved_qty', 10, 3)->default(0.000)->after('available_qty')->comment('Cantidad reservada');
            $table->decimal('sold_qty', 10, 3)->default(0.000)->after('reserved_qty')->comment('Cantidad vendida');
            $table->string('location')->nullable()->after('sold_qty')->comment('Ubicación del contenedor');
            $table->timestamp('last_movement_at')->nullable()->after('location');
            $table->foreignId('last_movement_by')->nullable()->after('last_movement_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_current_states', function (Blueprint $table) {
            $table->dropForeign(['last_movement_by']);
            $table->dropColumn([
                'available_qty',
                'reserved_qty',
                'sold_qty',
                'location',
                'last_movement_at',
                'last_movement_by',
            ]);
        });
    }
};
