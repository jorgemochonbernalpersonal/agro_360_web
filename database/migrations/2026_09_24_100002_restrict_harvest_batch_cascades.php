<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defensa en profundidad: cambia grape_reception_batches.campaign_id de
 * onDelete('cascade') a restrictOnDelete().
 *
 * El guard de Livewire\Winery\Harvest\Campaigns\Index::delete() ya bloquea el
 * borrado de una campaña con recepciones (activities o grape_reception_batches
 * asociados). Este cambio de FK añade una segunda barrera a nivel de BD: si
 * algún otro camino (comando artisan, tinker, código futuro) borra una
 * Campaign directamente sin pasar por ese guard, la base de datos rechaza el
 * borrado en vez de arrastrar en cascada los lotes de recepción de bodega
 * (registros INFOVI/SICILIE) asociados a esa campaña.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->onDelete('cascade');
        });
    }
};
