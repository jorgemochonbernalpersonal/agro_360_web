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
        if (!Schema::hasTable('viticulturist_hierarchy')) {
            return;
        }
        
        Schema::table('viticulturist_hierarchy', function (Blueprint $table) {
            $table->integer('winery_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('viticulturist_hierarchy')) {
            return;
        }
        
        Schema::table('viticulturist_hierarchy', function (Blueprint $table) {
            // Eliminar relaciones sin winery antes de hacer NOT NULL
            \DB::table('viticulturist_hierarchy')->whereNull('winery_id')->delete();
            $table->integer('winery_id')->nullable(false)->change();
        });
    }
};


