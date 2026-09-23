<?php

use Database\Seeders\MunicipalityCoordinatesSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Las alertas de mildiu/oídio necesitan coordenadas de municipio, pero el seeder
 * de coordenadas nunca se ejecutaba en los despliegues. Idempotente: solo rellena
 * municipios sin lat/lng.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new MunicipalityCoordinatesSeeder)->run();
    }

    public function down(): void
    {
        // Datos de referencia: no se borran.
    }
};
