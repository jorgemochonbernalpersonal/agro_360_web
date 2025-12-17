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
        Schema::table('crews', function (Blueprint $table) {
            $table->integer('winery_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crews', function (Blueprint $table) {
            // Eliminar cuadrillas sin winery antes de hacer NOT NULL
            \DB::table('crews')->whereNull('winery_id')->delete();
            $table->integer('winery_id')->nullable(false)->change();
        });
    }
};
