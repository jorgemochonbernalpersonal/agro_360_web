<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Riesgo de mildiu/oídio para una ubicación: meteo horaria de Open-Meteo
 * (7 días de histórico + previsión) pasada por DiseaseRiskModel.
 *
 * A diferencia de RemoteSensing\WeatherService, aquí NUNCA se inventan datos:
 * si la API falla se devuelve null y quien llama decide (no avisar, mostrar error).
 */
class DiseaseRiskForecast
{
    private const URL = 'https://api.open-meteo.com/v1/forecast';

    private const PAST_DAYS = 7;

    private const FORECAST_DAYS = 6;

    private const CACHE_TTL = 3 * 3600;

    public function __construct(private DiseaseRiskModel $model) {}

    /**
     * @return array<int, array<string, mixed>>|null días desde hoy, o null si no hay datos
     */
    public function forLocation(float $lat, float $lng): ?array
    {
        // ~1 km de precisión basta para meteo y comparte caché entre vecinos.
        $lat = round($lat, 2);
        $lng = round($lng, 2);
        $key = "disease-risk:{$lat}:{$lng}";

        $hourly = Cache::get($key);
        if ($hourly === null) {
            $hourly = $this->fetch($lat, $lng);
            if ($hourly === null) {
                return null;
            }
            Cache::put($key, $hourly, self::CACHE_TTL);
        }

        $days = $this->model->evaluate(
            $hourly['time'],
            $hourly['temperature_2m'],
            $hourly['relative_humidity_2m'],
            $hourly['precipitation'],
            now('Europe/Madrid')->toDateString(),
        );

        return $days !== [] ? $days : null;
    }

    /** @return array<string, array<int, mixed>>|null */
    private function fetch(float $lat, float $lng): ?array
    {
        try {
            // Open-Meteo devuelve algún 503 puntual: 2 reintentos antes de rendirse.
            $client = Http::timeout(15)->retry(3, 1000, throw: false);
            if (app()->environment('local')) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get(self::URL, [
                'latitude' => $lat,
                'longitude' => $lng,
                'hourly' => 'temperature_2m,relative_humidity_2m,precipitation',
                'past_days' => self::PAST_DAYS,
                'forecast_days' => self::FORECAST_DAYS,
                'timezone' => 'Europe/Madrid',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Alertas mildiu/oídio: Open-Meteo no disponible', ['error' => $e->getMessage()]);

            return null;
        }

        $hourly = $response->successful() ? ($response->json('hourly') ?? null) : null;

        foreach (['time', 'temperature_2m', 'relative_humidity_2m', 'precipitation'] as $field) {
            if (! is_array($hourly[$field] ?? null)) {
                Log::warning('Alertas mildiu/oídio: respuesta de Open-Meteo incompleta', ['status' => $response->status()]);

                return null;
            }
        }

        return $hourly;
    }
}
