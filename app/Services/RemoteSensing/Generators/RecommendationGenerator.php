<?php

namespace App\Services\RemoteSensing\Generators;

use App\Models\PlotRemoteSensing;

/**
 * Generates actionable recommendations based on remote sensing and weather data
 */
class RecommendationGenerator
{
    /**
     * Generate recommendations for a plot
     *
     * @param PlotRemoteSensing|null $ndviData NDVI data
     * @param array                  $weather  Weather data
     * @param array                  $soil     Soil data
     * @param array                  $forecast Weather forecast
     *
     * @return array Array of recommendations
     */
    public function generate(
        ?PlotRemoteSensing $ndviData,
        array $weather,
        array $soil,
        array $forecast
    ): array {
        $recommendations = [];

        // NDVI-based recommendations
        if ($ndviData) {
            $recommendations = array_merge(
                $recommendations,
                $this->generateNdviRecommendations($ndviData)
            );
        }

        // Temperature-based recommendations
        $recommendations = array_merge(
            $recommendations,
            $this->generateTemperatureRecommendations($weather)
        );

        // Soil moisture recommendations
        $recommendations = array_merge(
            $recommendations,
            $this->generateSoilRecommendations($soil)
        );

        // Precipitation forecast recommendations
        $recommendations = array_merge(
            $recommendations,
            $this->generateRainRecommendations($forecast)
        );

        // If no issues, add positive message
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'icon' => '✅',
                'title' => __('Condiciones óptimas'),
                'text' => __('Todos los indicadores están en rangos normales.'),
            ];
        }

        return $recommendations;
    }

    /**
     * Get water stress status
     *
     * @param array $soil  Soil data
     * @param array $solar Solar data
     *
     * @return array Status with emoji, text, color, and bg
     */
    public function getWaterStressStatus(array $soil, array $solar): array
    {
        $moisture = $soil['soil_moisture'] ?? 50;
        $et0 = $solar['et0'] ?? 3;

        // Water stress index: higher = more stress
        $stressIndex = ($et0 * 10) - $moisture;

        return match (true) {
            $stressIndex <= 0 => [
                'status' => 'optimal',
                'emoji' => '💧',
                'text' => __('Óptimo'),
                'color' => 'text-green-600',
                'bg' => 'bg-green-100',
            ],
            $stressIndex <= 20 => [
                'status' => 'mild',
                'emoji' => '💦',
                'text' => __('Leve'),
                'color' => 'text-yellow-600',
                'bg' => 'bg-yellow-100',
            ],
            $stressIndex <= 40 => [
                'status' => 'moderate',
                'emoji' => '🏜️',
                'text' => __('Moderado'),
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-100',
            ],
            default => [
                'status' => 'severe',
                'emoji' => '⚠️',
                'text' => __('Severo'),
                'color' => 'text-red-600',
                'bg' => 'bg-red-100',
            ],
        };
    }

    /**
     * Generate NDVI-based recommendations
     */
    private function generateNdviRecommendations(PlotRemoteSensing $ndviData): array
    {
        $recommendations = [];
        $ndvi = $ndviData->ndvi_mean ?? 0;

        if ($ndvi < 0.3) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🌱',
                'title' => __('Vigor bajo detectado'),
                'text' => sprintf(
                    'El NDVI es bajo (%.2f). Revisa posibles deficiencias nutricionales o estrés hídrico.',
                    $ndvi
                ),
            ];
        } elseif ($ndvi < 0.4 && in_array($ndviData->health_status, ['poor', 'moderate'])) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '📊',
                'title' => __('Vigor moderado'),
                'text' => sprintf(
                    'El NDVI es moderado (%.2f). Considera un análisis foliar para verificar nutrición.',
                    $ndvi
                ),
            ];
        }

        // Trend-based recommendations
        if ($ndviData->trend === 'decreasing') {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '📉',
                'title' => __('Tendencia decreciente'),
                'text' => __('El vigor vegetativo está disminuyendo. Monitoriza la evolución en los próximos días.'),
            ];
        }

        return $recommendations;
    }

    /**
     * Generate temperature-based recommendations
     */
    private function generateTemperatureRecommendations(array $weather): array
    {
        $recommendations = [];
        $temp = $weather['temperature'] ?? 20;

        if ($temp < 0) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => '❄️',
                'title' => __('Riesgo de helada'),
                'text' => sprintf(
                    'Temperatura bajo cero (%.1f°C). Considera medidas de protección contra heladas.',
                    $temp
                ),
            ];
        } elseif ($temp < 5) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🌡️',
                'title' => __('Temperaturas bajas'),
                'text' => sprintf(
                    'Temperatura muy baja (%.1f°C). Monitoriza riesgo de heladas nocturnas.',
                    $temp
                ),
            ];
        } elseif ($temp > 35) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🔥',
                'title' => __('Estrés térmico'),
                'text' => sprintf(
                    'Temperatura elevada (%.1f°C). Monitoriza riego y posible estrés hídrico.',
                    $temp
                ),
            ];
        } elseif ($temp > 40) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => '🔥',
                'title' => __('Calor extremo'),
                'text' => sprintf(
                    'Temperatura muy alta (%.1f°C). Riego urgente y protección contra insolación.',
                    $temp
                ),
            ];
        }

        return $recommendations;
    }

    /**
     * Generate soil moisture recommendations
     */
    private function generateSoilRecommendations(array $soil): array
    {
        $recommendations = [];
        $moisture = $soil['soil_moisture'] ?? 30;

        if ($moisture < 15) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '💧',
                'title' => __('Suelo seco'),
                'text' => sprintf(
                    'Humedad del suelo baja (%.0f%%). Considera riego para evitar estrés hídrico.',
                    $moisture
                ),
            ];
        } elseif ($moisture < 10) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => '🏜️',
                'title' => __('Suelo muy seco'),
                'text' => sprintf(
                    'Humedad del suelo crítica (%.0f%%). Riego urgente necesario.',
                    $moisture
                ),
            ];
        } elseif ($moisture > 60) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '💦',
                'title' => __('Suelo húmedo'),
                'text' => sprintf(
                    'Alta humedad del suelo (%.0f%%). Evita riego para prevenir encharcamiento.',
                    $moisture
                ),
            ];
        } elseif ($moisture > 80) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🌊',
                'title' => __('Suelo saturado'),
                'text' => sprintf(
                    'Humedad del suelo muy alta (%.0f%%). Riesgo de encharcamiento y asfixia radicular.',
                    $moisture
                ),
            ];
        }

        return $recommendations;
    }

    /**
     * Generate precipitation forecast recommendations
     */
    private function generateRainRecommendations(array $forecast): array
    {
        $recommendations = [];

        // Count rainy days (>5mm)
        $rainDays = collect($forecast)->filter(
            fn ($day) => ($day['precipitation'] ?? 0) > 5
        )->count();

        // Calculate total expected precipitation
        $totalRain = collect($forecast)->sum(fn ($day) => $day['precipitation'] ?? 0);

        if ($rainDays >= 3) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '🌧️',
                'title' => __('Lluvia prevista'),
                'text' => sprintf(
                    'Se esperan %d días de lluvia esta semana (%.1f mm total). Planifica tratamientos fitosanitarios.',
                    $rainDays,
                    $totalRain
                ),
            ];
        } elseif ($totalRain > 50) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '⛈️',
                'title' => __('Lluvias intensas'),
                'text' => sprintf(
                    'Se esperan lluvias abundantes (%.1f mm). Verifica drenaje y pospón tratamientos.',
                    $totalRain
                ),
            ];
        } elseif ($rainDays === 0 && count($forecast) >= 7) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '☀️',
                'title' => __('Semana seca'),
                'text' => __('No se espera lluvia en los próximos 7 días. Monitoriza necesidades de riego.'),
            ];
        }

        return $recommendations;
    }
}
