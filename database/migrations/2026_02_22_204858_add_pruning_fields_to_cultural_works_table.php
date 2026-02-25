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
            $table->string('pruning_type', 50)->nullable()->after('work_type')
                ->comment('Tipo de poda: guyot, doble_guyot, vaso, cordon, other');
            $table->integer('productive_buds_per_hectare')->nullable()->after('pruning_type')
                ->comment('Yemas productivas/ha resultantes de la poda');
        });
    }

    public function down(): void
    {
        Schema::table('cultural_works', function (Blueprint $table) {
            $table->dropColumn(['pruning_type', 'productive_buds_per_hectare']);
        });
    }
};
