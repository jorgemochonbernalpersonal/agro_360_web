<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insertar materiales comunes
        DB::table('container_materials')->insert([
            ['name' => 'Roble Francés', 'description' => 'Roble francés para barricas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Roble Americano', 'description' => 'Roble americano para barricas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Roble Húngaro', 'description' => 'Roble húngaro para barricas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Acero Inoxidable', 'description' => 'Acero inoxidable 304/316', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hormigón', 'description' => 'Hormigón para tinas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cerámica', 'description' => 'Cerámica para ánforas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fibra de Vidrio', 'description' => 'Fibra de vidrio', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('container_materials');
    }
};
