<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            // Aplicador ROPO vinculado (FK a field_applicators)
            $table->foreignId('field_applicator_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('field_applicators')
                ->nullOnDelete();

            // Zona tampón (requisito hídrico de algunos productos)
            $table->boolean('buffer_zone_respected')->nullable()->after('water_volume_liters_ha');
            $table->decimal('distance_to_water_m', 6, 2)->nullable()->after('buffer_zone_respected');

            // Renombrar advisory_action_date → advisory_recommendation_date
            $table->renameColumn('advisory_action_date', 'advisory_recommendation_date');
        });
    }

    public function down(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->dropForeign(['field_applicator_id']);
            $table->dropColumn('field_applicator_id');
            $table->dropColumn('buffer_zone_respected');
            $table->dropColumn('distance_to_water_m');
            $table->renameColumn('advisory_recommendation_date', 'advisory_action_date');
        });
    }
};
