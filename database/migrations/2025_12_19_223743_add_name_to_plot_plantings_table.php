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
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->string('name')->nullable()->after('plot_id')
                ->comment('Nombre identificativo de la plantación para diferenciarla (ej: "Parcela Norte - Tempranillo", "Bloque A", etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
