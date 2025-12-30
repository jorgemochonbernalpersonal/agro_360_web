<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units_of_measurement', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('symbol', 10);
            $table->string('type')->comment('volume, weight, etc.');
            $table->timestamps();
        });

        // Insertar unidades comunes
        DB::table('units_of_measurement')->insert([
            ['name' => 'Litros', 'symbol' => 'L', 'type' => 'volume', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilogramos', 'symbol' => 'kg', 'type' => 'weight', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hectolitros', 'symbol' => 'hL', 'type' => 'volume', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('units_of_measurement');
    }
};
