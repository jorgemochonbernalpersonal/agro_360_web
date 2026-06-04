<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pobla lat/lng en municipalities usando database/data/municipios.csv
 *
 * Formato CSV (sin cabecera, separador ;):
 *   ComunidadAutonoma;Provincia;Municipio;Lat;Lng;Altitud;PobTotal;PobHombre;PobMujer
 *
 * Fuente: https://gist.github.com/soft2help/6f5fd0a2cb6d02da3e87fb61edcc4353
 */
class MunicipalityCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/municipios.csv');

        if (! file_exists($csvPath)) {
            $this->command->error('Archivo no encontrado: database/data/municipios.csv');

            return;
        }

        $handle = fopen($csvPath, 'r');
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 5) {
                continue;
            }

            $municipioName = trim($row[2]);
            $lat = (float) $row[3];
            $lng = (float) $row[4];

            if ($lat === 0.0 && $lng === 0.0) {
                continue;
            }

            $affected = DB::table('municipalities')
                ->where('name', $municipioName)
                ->whereNull('lat')
                ->update(['lat' => round($lat, 6), 'lng' => round($lng, 6)]);

            $affected ? $updated++ : $skipped++;
        }

        fclose($handle);
        $this->command->info("Actualizados: {$updated} | Ya tenían coords o no encontrados: {$skipped}");
    }
}
