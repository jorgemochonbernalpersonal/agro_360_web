<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;

/**
 * Helper centralizado para obtener coordenadas de parcelas
 * Elimina duplicación en todos los servicios NASA y Weather
 */
class CoordinatesHelper
{
    /**
     * Coordenadas por defecto (Ribera del Duero, España)
     */
    private const DEFAULT_LAT = 41.6167;
    private const DEFAULT_LON = -3.7033;

    /**
     * Obtener coordenadas de una parcela desde su geometría
     * 
     * @param Plot $plot
     * @param int|null $recintoId ID de MultipartPlotSigpac (recinto) para usar su PlotGeometry
     * @param array|null $overrideCoords ['lat' => float, 'lng'|'lon' => float] para forzar coordenadas
     * @return array ['lat' => float, 'lon' => float]
     */
    public static function getCoordinates(Plot $plot, ?int $recintoId = null, ?array $overrideCoords = null): array
    {
        // 1. Override explícito (p.ej. desde recinto seleccionado en Dashboard)
        if ($overrideCoords && isset($overrideCoords['lat'])) {
            $lon = $overrideCoords['lng'] ?? $overrideCoords['lon'] ?? self::DEFAULT_LON;
            if (self::isValidCoordinate($overrideCoords['lat'], $lon)) {
                return ['lat' => $overrideCoords['lat'], 'lon' => $lon];
            }
        }

        // 2. Coordenadas del recinto seleccionado (MultipartPlotSigpac → PlotGeometry)
        if ($recintoId) {
            $mps = $plot->multiplePlotSigpacs()->with('plotGeometry')->find($recintoId);
            if ($mps?->plotGeometry) {
                $centroid = $mps->plotGeometry->getCentroidAsArray();
                if ($centroid && self::isValidCoordinate($centroid['lat'], $centroid['lng'])) {
                    return ['lat' => $centroid['lat'], 'lon' => $centroid['lng']];
                }
            }
        }

        // 3. Primera geometría de la parcela (plotGeometries vía multipart)
        $geometry = $plot->plotGeometries()->first();
        if ($geometry) {
            $centroid = $geometry->getCentroidAsArray();
            if ($centroid && self::isValidCoordinate($centroid['lat'], $centroid['lng'])) {
                return ['lat' => $centroid['lat'], 'lon' => $centroid['lng']];
            }
        }

        return ['lat' => self::DEFAULT_LAT, 'lon' => self::DEFAULT_LON];
    }

    /**
     * Obtener coordenadas con fallback a geocoding por municipio
     * Usado principalmente por WeatherService
     * 
     * @param Plot $plot
     * @return array ['lat' => float, 'lon' => float]
     */
    public static function getCoordinatesWithGeocoding(Plot $plot): array
    {
        // Primero intentar desde geometría
        $coords = self::getCoordinates($plot);
        
        // Si no son las coordenadas por defecto, retornar
        if ($coords['lat'] !== self::DEFAULT_LAT || $coords['lon'] !== self::DEFAULT_LON) {
            return $coords;
        }

        // Intentar geocoding por municipio (solo si plot tiene municipio)
        if ($plot->municipality && $plot->province) {
            $geocoded = self::geocodeByMunicipality($plot);
            if ($geocoded) {
                return $geocoded;
            }
        }

        // Fallback final
        return $coords;
    }

    /**
     * Geocodificar ubicación por municipio usando Open-Meteo
     * 
     * @param Plot $plot
     * @return array|null ['lat' => float, 'lon' => float] o null si falla
     */
    private static function geocodeByMunicipality(Plot $plot): ?array
    {
        $query = "{$plot->municipality->name}, {$plot->province->name}, Spain";
        $cacheKey = "geo_loc_" . \Illuminate\Support\Str::slug($query);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400 * 30, function () use ($query) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->get('https://geocoding-api.open-meteo.com/v1/search', [
                        'name' => $query,
                        'count' => 1,
                        'language' => 'es',
                        'format' => 'json'
                    ]);

                if ($response->successful() && isset($response['results'][0])) {
                    $result = $response['results'][0];
                    return [
                        'lat' => $result['latitude'],
                        'lon' => $result['longitude']
                    ];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Geocoding failed for: $query", [
                    'error' => $e->getMessage()
                ]);
            }
            
            return null;
        });
    }

    /**
     * Validar que las coordenadas estén en rangos válidos
     * 
     * @param float $lat Latitud (-90 a 90)
     * @param float $lon Longitud (-180 a 180)
     * @return bool
     */
    public static function isValidCoordinate(float $lat, float $lon): bool
    {
        return $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180;
    }

    /**
     * Formatear coordenadas para display
     * 
     * @param float $lat
     * @param float $lon
     * @return string "41.6167°N, 3.7033°W"
     */
    public static function format(float $lat, float $lon): string
    {
        $latDir = $lat >= 0 ? 'N' : 'S';
        $lonDir = $lon >= 0 ? 'E' : 'W';
        
        return sprintf(
            "%.4f°%s, %.4f°%s",
            abs($lat),
            $latDir,
            abs($lon),
            $lonDir
        );
    }

    /**
     * Calcular distancia entre dos puntos (en km)
     * Fórmula de Haversine
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distancia en kilómetros
     */
    public static function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Obtener coordenadas por defecto
     * 
     * @return array ['lat' => float, 'lon' => float]
     */
    public static function getDefaultCoordinates(): array
    {
        return [
            'lat' => self::DEFAULT_LAT,
            'lon' => self::DEFAULT_LON,
        ];
    }
}
