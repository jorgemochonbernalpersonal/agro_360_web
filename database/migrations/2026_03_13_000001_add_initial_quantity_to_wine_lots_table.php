<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->decimal('initial_quantity', 10, 3)
                ->default(0)
                ->after('quantity')
                ->comment('Stock inicial al crear el lote. Inmutable.');
        });

        // Backfill: los lotes existentes toman quantity como punto de partida
        DB::statement('UPDATE wine_lots SET initial_quantity = quantity');
    }

    public function down(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->dropColumn('initial_quantity');
        });
    }
};
