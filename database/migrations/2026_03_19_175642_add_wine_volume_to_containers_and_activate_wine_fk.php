<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. wine_volume_liters en containers — litros de vino elaborado en el depósito
        Schema::table('containers', function (Blueprint $table) {
            $table->decimal('wine_volume_liters', 12, 3)
                ->default(0)
                ->after('used_capacity')
                ->comment('Litros de vino elaborado actualmente en este contenedor');
        });

        // 2. Activar FK wine_id en container_current_states (tabla wines ya existe)
        Schema::table('container_current_states', function (Blueprint $table) {
            $table->foreign('wine_id')
                ->references('id')->on('wines')
                ->onDelete('set null');
        });

        // 3. Activar FK wine_id en container_histories
        Schema::table('container_histories', function (Blueprint $table) {
            $table->foreign('wine_id')
                ->references('id')->on('wines')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('container_histories', function (Blueprint $table) {
            $table->dropForeign(['wine_id']);
        });
        Schema::table('container_current_states', function (Blueprint $table) {
            $table->dropForeign(['wine_id']);
        });
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn('wine_volume_liters');
        });
    }
};
