<?php

namespace App\Console\Commands;

use App\Models\Municipality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GeocodeMunicipalities extends Command
{
    protected $signature   = 'municipalities:geocode {--force : Regeocoda incluso los que ya tienen coordenadas}';
    protected $description = 'Rellena lat/lng de municipios usando Nominatim (OpenStreetMap)';

    public function handle(): int
    {
        $query = Municipality::with('province')
            ->when(! $this->option('force'), fn ($q) => $q->whereNull('lat'));

        $total = $query->count();

        if ($total === 0) {
            $this->info('Todos los municipios ya tienen coordenadas.');
            return self::SUCCESS;
        }

        $this->info("Geocodificando {$total} municipios...");
        $bar     = $this->output->createProgressBar($total);
        $updated = 0;
        $failed  = 0;

        $query->chunk(50, function ($municipalities) use ($bar, &$updated, &$failed) {
            foreach ($municipalities as $municipality) {
                $result = $this->geocode($municipality);

                if ($result) {
                    $municipality->update($result);
                    $updated++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("Sin resultado: {$municipality->name}");
                }

                $bar->advance();
                usleep(1_100_000); // Nominatim: max 1 req/s
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Actualizados: {$updated} | Sin resultado: {$failed}");

        return self::SUCCESS;
    }

    private function geocode(Municipality $municipality): ?array
    {
        $http = Http::withHeaders([
            'User-Agent'      => 'Agro365/1.0 (info@agro365.es)',
            'Accept-Language' => 'es',
        ]);

        if (app()->environment('local')) {
            $http = $http->withoutVerifying();
        }

        // Limpia nombres bilingües: "Ayala/Aiara" → "Ayala", "Araba/Álava" → "Araba"
        $municipalityName = $this->cleanName($municipality->name);
        $provinceName     = $this->cleanName($municipality->province?->name ?? '');

        // Intento 1: municipio + provincia
        $results = $http->get('https://nominatim.openstreetmap.org/search', [
            'city'         => $municipalityName,
            'state'        => $provinceName,
            'country'      => 'Spain',
            'format'       => 'json',
            'limit'        => 1,
            'addressdetails' => 0,
        ])->json();

        // Intento 2: solo nombre del municipio + Spain (fallback)
        if (empty($results)) {
            usleep(1_100_000);
            $results = $http->get('https://nominatim.openstreetmap.org/search', [
                'q'            => "{$municipalityName}, Spain",
                'format'       => 'json',
                'limit'        => 1,
                'countrycodes' => 'es',
            ])->json();
        }

        if (empty($results)) return null;

        return [
            'lat' => round((float) $results[0]['lat'], 6),
            'lng' => round((float) $results[0]['lon'], 6),
        ];
    }

    /** Toma solo la primera parte de nombres bilingues: "Ayala/Aiara" → "Ayala" */
    private function cleanName(string $name): string
    {
        return trim(explode('/', $name)[0]);
    }
}
