<?php

namespace App\Console\Commands;

use App\Models\Municipality;
use App\Models\Plot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GeocodePlotMunicipalities extends Command
{
    protected $signature = 'municipalities:geocode-plots';

    protected $description = 'Geocodifica solo los municipios que tienen parcelas (rápido)';

    public function handle(): int
    {
        $ids = Plot::whereNotNull('municipality_id')
            ->distinct()
            ->pluck('municipality_id');

        $municipalities = Municipality::whereIn('id', $ids)
            ->whereNull('lat')
            ->with('province')
            ->get();

        if ($municipalities->isEmpty()) {
            $this->info('Todos los municipios con parcelas ya tienen coordenadas.');

            return self::SUCCESS;
        }

        $this->info("Geocodificando {$municipalities->count()} municipio(s) con parcelas...");
        $updated = 0;

        foreach ($municipalities as $municipality) {
            $province = $municipality->province?->name ?? '';
            $search = "{$municipality->name}, {$province}, España";

            $http = Http::withHeaders([
                'User-Agent' => 'Agro365/1.0 (info@agro365.es)',
                'Accept-Language' => 'es',
            ]);

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get('https://nominatim.openstreetmap.org/search', [
                'q' => $search,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'es',
            ]);

            $results = $response->json();

            if (! empty($results)) {
                $municipality->update([
                    'lat' => round((float) $results[0]['lat'], 6),
                    'lng' => round((float) $results[0]['lon'], 6),
                ]);
                $this->line("✓ {$municipality->name}: {$results[0]['lat']}, {$results[0]['lon']}");
                $updated++;
            } else {
                $this->warn("Sin resultado: {$search}");
            }

            usleep(1_100_000);
        }

        $this->info("Listo. Actualizados: {$updated}");

        return self::SUCCESS;
    }
}
