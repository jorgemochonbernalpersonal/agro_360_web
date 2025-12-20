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
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['winery_id']);
        });
        
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->unsignedBigInteger('winery_id')->nullable()->change();
        });
        
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Recrear foreign key
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['winery_id']);
        });
        
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Eliminar relaciones sin winery antes de hacer NOT NULL
            \DB::table('winery_viticulturist')->whereNull('winery_id')->delete();
            $table->unsignedBigInteger('winery_id')->nullable(false)->change();
        });
        
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            // Recrear foreign key
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
