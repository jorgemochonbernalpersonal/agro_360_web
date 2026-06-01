<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * La columna campaign_id se añadió en 2025_12_17_103345 sin constraint de
     * integridad referencial. Esta migración crea la foreign key real.
     */
    public function up(): void
    {
        // Saneo: cualquier campaign_id huérfano (apunta a una campaña inexistente)
        // se pone a null para que la creación del constraint no falle.
        DB::table('agricultural_activities')
            ->whereNotNull('campaign_id')
            ->whereNotIn('campaign_id', function ($query) {
                $query->select('id')->from('campaigns');
            })
            ->update(['campaign_id' => null]);

        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
        });
    }
};
