<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_fermentation_controls', function (Blueprint $table) {
            $table->foreignId('container_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wine_fermentation_controls', function (Blueprint $table) {
            $table->foreignId('container_id')->nullable(false)->change();
        });
    }
};
