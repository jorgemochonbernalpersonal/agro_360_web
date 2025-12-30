<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insertar tipos comunes
        DB::table('container_types')->insert([
            ['name' => 'Barrica', 'description' => 'Barrica de madera para crianza', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Depósito', 'description' => 'Depósito de acero inoxidable', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tanque', 'description' => 'Tanque de fermentación', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tina', 'description' => 'Tina de hormigón', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ánfora', 'description' => 'Ánfora de cerámica', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('container_types');
    }
};
