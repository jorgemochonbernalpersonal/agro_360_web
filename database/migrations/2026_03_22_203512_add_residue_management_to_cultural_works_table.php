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
        Schema::table('cultural_works', function (Blueprint $table) {
            $table->string('residue_management', 30)->nullable()->after('productive_buds_per_hectare')
                ->comment('Gestión del ramón de poda — BCAM 6 PAC: triturado_incorporado, triturado_superficie, retirado, quemado, otro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultural_works', function (Blueprint $table) {
            $table->dropColumn('residue_management');
        });
    }
};
