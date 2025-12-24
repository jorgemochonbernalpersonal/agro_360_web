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
        Schema::table('irrigations', function (Blueprint $table) {
            // Campos PAC obligatorios para riego
            $table->string('water_source', 100)->nullable()->after('irrigation_method')
                ->comment('Origen del agua: pozo, embalse, acequia, río, etc.');
            
            $table->string('water_concession', 100)->nullable()->after('water_source')
                ->comment('Número de concesión o autorización de agua');
            
            $table->decimal('flow_rate', 10, 2)->nullable()->after('water_concession')
                ->comment('Caudal de riego en litros/hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('irrigations', function (Blueprint $table) {
            $table->dropColumn([
                'water_source',
                'water_concession',
                'flow_rate',
            ]);
        });
    }
};
