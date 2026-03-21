<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->enum('unit', ['kg', 'litros'])
                ->default('kg')
                ->after('capacity')
                ->comment('Unidad de la capacidad: kg para uva/mosto, litros para vino');
        });
    }

    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
