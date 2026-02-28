<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 20);
            $table->string('category', 20)->default('other'); // volume, weight, count, other
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('units')->insert([
            ['name' => 'Litros',     'symbol' => 'L',        'category' => 'volume', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mililitros', 'symbol' => 'mL',       'category' => 'volume', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilogramos', 'symbol' => 'kg',       'category' => 'weight', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gramos',     'symbol' => 'g',        'category' => 'weight', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unidades',   'symbol' => 'unidades', 'category' => 'count',  'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
