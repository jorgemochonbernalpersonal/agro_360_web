<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->decimal('affected_area_percentage', 5, 2)->nullable()->after('severity')
                ->comment('% de superficie afectada — IPM PAC');
            $table->boolean('threshold_exceeded')->default(false)->after('affected_area_percentage')
                ->comment('Umbral de daño económico superado — IPM PAC');
            $table->date('follow_up_date')->nullable()->after('threshold_exceeded')
                ->comment('Fecha de revisión programada');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn(['affected_area_percentage', 'threshold_exceeded', 'follow_up_date']);
        });
    }
};
