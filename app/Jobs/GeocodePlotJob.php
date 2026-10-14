<?php

namespace App\Jobs;

use App\Models\Municipality;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Geocodifica (lat/lng) un municipio vía Nominatim, en background.
 *
 * Se despacha desde PlotObserver al crear/editar una parcela: antes la
 * llamada HTTP se hacía en línea dentro de la transacción de guardado de la
 * parcela (timeout por defecto de 30s, bloqueando la petición del usuario).
 */
class GeocodePlotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Evita reintentar indefinidamente un municipio que Nominatim no reconoce. */
    private const NEGATIVE_CACHE_TTL_DAYS = 7;

    public function __construct(public int $municipalityId) {}

    public function handle(): void
    {
        $cacheKey = "nominatim_failed_municipality_{$this->municipalityId}";
        if (Cache::has($cacheKey)) {
            return;
        }

        $municipality = Municipality::with('province')->find($this->municipalityId);

        if (! $municipality || $municipality->lat !== null) {
            return;
        }

        try {
            $name = trim(explode('/', $municipality->name)[0]);
            $province = trim(explode('/', $municipality->province->name ?? '')[0]);

            $http = Http::withHeaders(['User-Agent' => 'Agro365/1.0 (info@agro365.es)'])
                ->timeout(5);

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $results = $http->get('https://nominatim.openstreetmap.org/search', [
                'city' => $name,
                'state' => $province,
                'country' => __('Spain'),
                'format' => 'json',
                'limit' => 1,
            ])->json();

            if (! empty($results)) {
                $municipality->update([
                    'lat' => round((float) $results[0]['lat'], 6),
                    'lng' => round((float) $results[0]['lon'], 6),
                ]);
            } else {
                // Resultado negativo: no reintentar en cada parcela nueva de este municipio.
                Cache::put($cacheKey, true, now()->addDays(self::NEGATIVE_CACHE_TTL_DAYS));
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo geocodificar municipio {$this->municipalityId}: {$e->getMessage()}");
        }
    }
}
