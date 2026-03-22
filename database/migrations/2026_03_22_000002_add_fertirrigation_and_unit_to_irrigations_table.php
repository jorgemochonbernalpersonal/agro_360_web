<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('irrigations', function (Blueprint $table) {
            $table->string('water_volume_unit', 3)->default('L')->after('water_volume');
            $table->boolean('is_fertirrigation')->default(false)->after('flow_rate');
            $table->string('fertilizer_product', 150)->nullable()->after('is_fertirrigation');
            $table->decimal('fertilizer_dose_per_ha', 8, 2)->nullable()->after('fertilizer_product');
        });
    }

    public function down(): void
    {
        Schema::table('irrigations', function (Blueprint $table) {
            $table->dropColumn(['water_volume_unit', 'is_fertirrigation', 'fertilizer_product', 'fertilizer_dose_per_ha']);
        });
    }
};
