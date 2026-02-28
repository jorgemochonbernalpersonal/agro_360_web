<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Litros, Mililitros, Kilogramos, Gramos, Unidades
            $table->string('symbol', 20);  // L, mL, kg, g, unidades  (lo que se guarda en product_stocks.unit)
            $table->string('category', 20)->default('other'); // volume, weight, count, other
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
