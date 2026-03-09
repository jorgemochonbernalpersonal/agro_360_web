<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->decimal('initial_quantity_kg', 12, 3)->nullable()
                ->after('volume_liters')
                ->comment('Kg de uva de entrada (trazabilidad)');
        });
    }

    public function down(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->dropColumn('initial_quantity_kg');
        });
    }
};
