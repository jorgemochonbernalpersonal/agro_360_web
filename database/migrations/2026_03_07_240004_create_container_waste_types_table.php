<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_waste_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed tipos básicos
        DB::table('container_waste_types')->insert([
            ['name' => 'Lías',              'description' => 'Residuo de levaduras y partículas tras fermentación', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Orujos',            'description' => 'Hollejos y pepitas tras prensado',                    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tartrato',          'description' => 'Depósitos de tartratos en paredes del contenedor',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agua de limpieza', 'description' => 'Aguas residuales del proceso de limpieza',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Restos de filtrado','description' => 'Tierras de diatomeas, placas filtrantes',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Otro',              'description' => 'Otro tipo de residuo',                                'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('container_waste_types');
    }
};
