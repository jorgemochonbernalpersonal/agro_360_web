<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->after('name');

            // Descripción y notas comerciales
            $table->text('description')->nullable()->after('notes');
            $table->text('pairing')->nullable()->after('description');
            $table->text('tasting_notes')->nullable()->after('pairing');
            $table->text('consumption_recommendation')->nullable()->after('tasting_notes');
            $table->decimal('recommended_temperature_min', 4, 1)->nullable()->after('consumption_recommendation');
            $table->decimal('recommended_temperature_max', 4, 1)->nullable()->after('recommended_temperature_min');
            $table->string('tags', 500)->nullable()->after('recommended_temperature_max');

            // Certificaciones adicionales (vinai compat)
            $table->boolean('sulfites')->default(false)->after('is_biodynamic');
            $table->boolean('ecological')->default(false)->after('sulfites');
        });
    }

    public function down(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->dropColumn([
                'sku',
                'description', 'pairing', 'tasting_notes', 'consumption_recommendation',
                'recommended_temperature_min', 'recommended_temperature_max', 'tags',
                'sulfites', 'ecological',
            ]);
        });
    }
};
