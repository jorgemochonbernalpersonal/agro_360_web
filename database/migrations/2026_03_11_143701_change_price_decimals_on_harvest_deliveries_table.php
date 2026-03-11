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
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->decimal('price_per_kg', 10, 3)->nullable()->change();
            $table->decimal('total_price', 12, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->decimal('price_per_kg', 10, 4)->nullable()->change();
            $table->decimal('total_price', 12, 2)->nullable()->change();
        });
    }
};
