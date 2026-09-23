<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pobla lat/lng en municipalities usando database/data/municipios.csv
 *
 * Formato CSV (sin cabecera, separador ;):
 *   ComunidadAutonoma;Provincia;Municipio;Lat;Lng;Altitud;PobTotal;PobHombre;PobMujer
 *
 * Fuente: https://gist.github.com/soft2help/6f5fd0a2cb6d02da3e87fb61edcc4353
 *
 * El cruce se hace por nombre de municipio + provincia (normalizados: sin tildes,
 * "Rozas, Las" = "Las Rozas", "Bizkaia" = "Vizcaya"...). Con solo el nombre, los
 * municipios homónimos de provincias distintas acababan con las mismas coordenadas.
 * Idempotente: solo rellena municipios sin coordenadas.
 */
class MunicipalityCoordinatesSeeder extends Seeder
{
    /** Nombres de provincia en euskera → castellano (el CSV usa el castellano). */
    private const PROVINCE_ALIASES = [
        'bizkaia' => 'vizcaya',
        'gipuzkoa' => 'guipuzcoa',
        'araba' => 'alava',
    ];

    public function run(): void
    {
        $csvPath = database_path('data/municipios.csv');

        if (! file_exists($csvPath)) {
            $this->command?->error('Archivo no encontrado: database/data/municipios.csv');

            return;
        }

        // provincia normalizada → id (cada provincia puede tener varios nombres: "Alicante/Alacant")
        $provinceIds = [];
        foreach (DB::table('provinces')->get(['id', 'name']) as $province) {
            foreach ($this->nameVariants($province->name) as $variant) {
                $provinceIds[$variant] = $province->id;
            }
        }

        // municipio normalizado → [ {id, province_id}, ... ] (los bilingües se indexan por cada nombre)
        $municipalities = [];
        foreach (DB::table('municipalities')->whereNull('lat')->get(['id', 'name', 'province_id']) as $m) {
            foreach (array_unique($this->nameVariants($m->name)) as $variant) {
                $municipalities[$variant][] = $m;
            }
        }

        $rows = [];
        $handle = fopen($csvPath, 'r');
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) >= 5) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        // El CSV tiene algunos municipios con las coordenadas de otro (copiadas):
        // mejor sin coordenadas que con las de un pueblo a 50 km.
        $coordCounts = array_count_values(array_map(fn (array $r) => $r[3].';'.$r[4], $rows));

        $updates = [];
        $skipped = 0;

        foreach ($rows as $row) {
            [$lat, $lng] = [(float) $row[3], (float) $row[4]];
            if ($coordCounts[$row[3].';'.$row[4]] > 1) {
                $skipped++;

                continue;
            }

            $candidates = [];
            foreach ($this->nameVariants($row[2]) as $variant) {
                foreach ($municipalities[$variant] ?? [] as $m) {
                    $candidates[$m->id] = $m;
                }
            }
            $candidates = array_values($candidates);

            if (($lat === 0.0 && $lng === 0.0) || $candidates === []) {
                $skipped++;

                continue;
            }

            $provinceId = null;
            foreach ($this->nameVariants($row[1]) as $variant) {
                $provinceId ??= $provinceIds[$variant] ?? null;
            }

            $matches = array_values(array_filter($candidates, fn ($m) => $m->province_id === $provinceId));

            // Sin provincia reconocible solo es seguro si el nombre es único en toda España.
            if ($matches === [] && count($candidates) === 1 && $provinceId === null) {
                $matches = $candidates;
            }

            if (count($matches) !== 1) {
                $skipped++;

                continue;
            }

            $updates[$matches[0]->id] = ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
        }

        // Un UPDATE con CASE por lote: miles de UPDATE sueltos tardaban ~1 min.
        foreach (array_chunk($updates, 500, true) as $chunk) {
            $latCase = $lngCase = '';
            $bindings = ['lat' => [], 'lng' => []];
            foreach ($chunk as $id => $coords) {
                $latCase .= ' WHEN ? THEN ?';
                $lngCase .= ' WHEN ? THEN ?';
                array_push($bindings['lat'], $id, $coords['lat']);
                array_push($bindings['lng'], $id, $coords['lng']);
            }
            $ids = array_keys($chunk);

            DB::update(
                "UPDATE municipalities SET lat = CASE id{$latCase} END, lng = CASE id{$lngCase} END
                 WHERE lat IS NULL AND id IN (".implode(',', array_fill(0, count($ids), '?')).')',
                [...$bindings['lat'], ...$bindings['lng'], ...$ids]
            );
        }

        $this->command?->info('Actualizados: '.count($updates)." | Sin coincidencia única: {$skipped}");
    }

    /**
     * "Alicante/Alacant" → [alicante, alacant]; "Coruña, A" → [a coruna]; "Bizkaia" → [bizkaia, vizcaya].
     *
     * @return array<int, string>
     */
    private function nameVariants(string $name): array
    {
        $variants = [];
        foreach (explode('/', $name) as $part) {
            $normalized = $this->normalize($part);
            $variants[] = $normalized;
            if (isset(self::PROVINCE_ALIASES[$normalized])) {
                $variants[] = self::PROVINCE_ALIASES[$normalized];
            }
        }

        return $variants;
    }

    /**
     * Minúsculas, sin tildes y con el artículo pospuesto delante:
     * "Rozas de Madrid, Las" (BD) y "Rozas de Madrid (Las)" (CSV) → "las rozas de madrid";
     * "Albagés, l'" → "l'albages".
     */
    private function normalize(string $name): string
    {
        $name = trim($name);
        if (preg_match('/^(.+?)\s*(?:,\s*(\S+)|\((\S+)\))$/u', $name, $m)) {
            $article = ($m[2] ?? '') !== '' ? $m[2] : $m[3];
            $name = $article.(str_ends_with($article, "'") ? '' : ' ').$m[1];
        }

        return Str::of(Str::ascii($name))->lower()->squish()->toString();
    }
}
