<?php

namespace App\Services\RemoteSensing\Detectors;

use App\Models\PlotRemoteSensing;
use Illuminate\Support\Collection;

/**
 * Anomaly Detection System for Remote Sensing Data
 *
 * Detects unusual patterns that may indicate:
 * - Disease outbreaks
 * - Pest infestations
 * - Nutrient deficiencies
 * - Water stress
 * - Equipment failures (irrigation)
 */
class AnomalyDetector
{
    // Thresholds for anomaly detection
    private const NDVI_DROP_THRESHOLD = 15;        // % drop to trigger alert

    private const RAPID_CHANGE_DAYS = 7;            // Days to consider "rapid"

    private const STATISTICAL_SIGMA = 2.0;          // Standard deviations for outlier

    private const MIN_HISTORICAL_POINTS = 10;       // Minimum data points needed

    /**
     * Detect all types of anomalies
     *
     * @param PlotRemoteSensing $current    Current observation
     * @param Collection        $historical Historical observations (last 90 days)
     *
     * @return array Detected anomalies
     */
    public function detectAnomalies(
        PlotRemoteSensing $current,
        Collection $historical
    ): array {
        $anomalies = [];

        // Rapid NDVI decline (disease/pest)
        if ($ndviAnomaly = $this->detectRapidNDVIDecline($current, $historical)) {
            $anomalies[] = $ndviAnomaly;
        }

        // Statistical outliers
        if ($outliers = $this->detectStatisticalOutliers($current, $historical)) {
            $anomalies = array_merge($anomalies, $outliers);
        }

        // Sudden water stress
        if ($waterStress = $this->detectSuddenWaterStress($current, $historical)) {
            $anomalies[] = $waterStress;
        }

        // Unusual temperature patterns
        if ($tempAnomaly = $this->detectTemperatureAnomaly($current, $historical)) {
            $anomalies[] = $tempAnomaly;
        }

        // Spatial anomalies (within-field variation)
        if ($spatialAnomaly = $this->detectSpatialAnomaly($current)) {
            $anomalies[] = $spatialAnomaly;
        }

        return [
            'has_anomalies' => ! empty($anomalies),
            'count' => count($anomalies),
            'anomalies' => $anomalies,
            'risk_level' => $this->calculateRiskLevel($anomalies),
        ];
    }

    /**
     * Generate alert message for user notification
     */
    public function generateAlertMessage(array $anomaly): string
    {
        return sprintf(
            "⚠️ [%s] %s\n%s\n\nAcción recomendada: %s",
            strtoupper($anomaly['severity']),
            $anomaly['title'],
            $anomaly['description'],
            $anomaly['recommended_actions'][0] ?? 'Revisar la parcela'
        );
    }

    /**
     * Check if anomaly should trigger immediate notification
     */
    public function shouldNotify(array $anomaly): bool
    {
        return in_array($anomaly['severity'], ['critical', 'high']);
    }

    /**
     * Detect rapid NDVI decline
     * Most critical: indicates disease or pest damage
     */
    private function detectRapidNDVIDecline(
        PlotRemoteSensing $current,
        Collection $historical
    ): ?array {
        if ($historical->count() < 3) {
            return null;
        }

        // Get recent baseline (7-14 days ago)
        $recentBaseline = $historical
            ->filter(fn ($d) => $d->image_date && $d->image_date->between(
                now()->subDays(14),
                now()->subDays(7)
            ))
            ->avg('ndvi_mean');

        if (! $recentBaseline) {
            return null;
        }

        $currentNDVI = (float) ($current->ndvi_mean ?? 0);
        $drop = $recentBaseline - $currentNDVI;
        $dropPercent = ($drop / $recentBaseline) * 100;

        // Significant drop detected
        if ($dropPercent >= self::NDVI_DROP_THRESHOLD) {
            return [
                'type' => 'rapid_ndvi_decline',
                'severity' => match (true) {
                    $dropPercent >= 30 => 'critical',
                    $dropPercent >= 20 => 'high',
                    default => 'medium',
                },
                'title' => __('Caída Rápida de Vigor'),
                'description' => sprintf(
                    'NDVI cayó %.1f%% en los últimos 7 días (%.3f → %.3f)',
                    $dropPercent,
                    $recentBaseline,
                    $currentNDVI
                ),
                'detected_at' => now()->toIso8601String(),
                'metrics' => [
                    'drop_percent' => round($dropPercent, 1),
                    'baseline_ndvi' => round($recentBaseline, 3),
                    'current_ndvi' => round($currentNDVI, 3),
                ],
                'probable_causes' => $this->identifyNDVIDropCauses($dropPercent, $current),
                'recommended_actions' => [
                    '🔍 Inspección visual urgente de la parcela',
                    '🦠 Buscar signos de enfermedades foliares (mildiu, oídio)',
                    '🐛 Revisar presencia de plagas (polilla del racimo, trips)',
                    '💧 Verificar sistema de riego (posible fallo)',
                ],
                'icon' => '🚨',
                'color' => 'red',
            ];
        }

        return null;
    }

    /**
     * Identify probable causes of NDVI drop
     */
    private function identifyNDVIDropCauses(float $dropPercent, PlotRemoteSensing $data): array
    {
        $causes = [];

        // Very rapid drop = likely disease or pest
        if ($dropPercent > 25) {
            $causes[] = [
                'cause' => __('Enfermedad fúngica grave'),
                'probability' => 'high',
                'examples' => __('Mildiu severo, Oídio avanzado'),
            ];
            $causes[] = [
                'cause' => __('Ataque de plaga masivo'),
                'probability' => 'high',
                'examples' => __('Polilla del racimo, Langosta'),
            ];
        }

        // Moderate drop + low soil moisture = water stress
        if ($dropPercent > 15 && $data->soil_moisture_mean < 0.15) {
            $causes[] = [
                'cause' => __('Estrés hídrico severo'),
                'probability' => 'high',
                'examples' => __('Fallo de riego, Sequía'),
            ];
        }

        // Moderate drop + normal moisture = disease
        if ($dropPercent > 15 && $data->soil_moisture_mean >= 0.15) {
            $causes[] = [
                'cause' => __('Enfermedad foliar'),
                'probability' => 'medium',
                'examples' => __('Mildiu, Botritis, Yesca'),
            ];
        }

        // High temperature stress
        if ($data->temperature_mean > 35) {
            $causes[] = [
                'cause' => __('Daño por calor extremo'),
                'probability' => 'medium',
                'examples' => __('Quemaduras foliares'),
            ];
        }

        return $causes;
    }

    /**
     * Detect statistical outliers using Z-score
     */
    private function detectStatisticalOutliers(
        PlotRemoteSensing $current,
        Collection $historical
    ): array {
        if ($historical->count() < self::MIN_HISTORICAL_POINTS) {
            return [];
        }

        $anomalies = [];
        $metrics = ['ndvi_mean', 'ndwi_mean', 'evi_mean', 'temperature_mean'];

        foreach ($metrics as $metric) {
            $values = $historical->pluck($metric)->filter()->values();

            if ($values->count() < self::MIN_HISTORICAL_POINTS) {
                continue;
            }

            $mean = $values->avg();
            $stdDev = $this->calculateStdDev($values->toArray());

            if ($stdDev === 0.0) {
                continue;
            }

            $currentValue = $current->$metric;
            $zScore = ($currentValue - $mean) / $stdDev;

            // Outlier detected (beyond ±2 sigma)
            if (abs($zScore) > self::STATISTICAL_SIGMA) {
                $anomalies[] = [
                    'type' => 'statistical_outlier',
                    'severity' => abs($zScore) > 3 ? 'high' : 'medium',
                    'title' => __('Valor Estadísticamente Anómalo'),
                    'description' => sprintf(
                        '%s fuera de rango normal (Z-score: %.1f)',
                        $this->getMetricLabel($metric),
                        $zScore
                    ),
                    'detected_at' => now()->toIso8601String(),
                    'metrics' => [
                        'metric' => $metric,
                        'current_value' => round($currentValue, 3),
                        'expected_mean' => round($mean, 3),
                        'std_dev' => round($stdDev, 3),
                        'z_score' => round($zScore, 2),
                    ],
                    'icon' => '📊',
                    'color' => 'orange',
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Detect sudden water stress
     */
    private function detectSuddenWaterStress(
        PlotRemoteSensing $current,
        Collection $historical
    ): ?array {
        if ($historical->count() < 5) {
            return null;
        }

        // Get recent average NDWI
        $recentNDWI = $historical
            ->sortByDesc('date')
            ->take(5)
            ->avg('ndwi_mean');

        $currentNDWI = (float) ($current->ndwi_mean ?? 0);
        $drop = $recentNDWI - $currentNDWI;

        // NDWI drop + low soil moisture = water stress
        if ($drop > 0.15 && $current->soil_moisture_mean < 0.20) {
            return [
                'type' => 'water_stress',
                'severity' => $current->soil_moisture_mean < 0.15 ? 'high' : 'medium',
                'title' => __('Estrés Hídrico Detectado'),
                'description' => sprintf(
                    'NDWI cayó %.2f y humedad del suelo al %.0f%%',
                    $drop,
                    $current->soil_moisture_mean * 100
                ),
                'detected_at' => now()->toIso8601String(),
                'metrics' => [
                    'ndwi_drop' => round($drop, 3),
                    'soil_moisture_percent' => round($current->soil_moisture_mean * 100, 1),
                    'current_ndwi' => round($currentNDWI, 3),
                ],
                'recommended_actions' => [
                    '💧 Verificar funcionamiento del sistema de riego',
                    '📊 Revisar programación de riego',
                    '🔍 Inspeccionar goteros (posible obstrucción)',
                    '⚠️ Considerar riego de emergencia',
                ],
                'icon' => '💧',
                'color' => 'blue',
            ];
        }

        return null;
    }

    /**
     * Detect temperature anomalies
     */
    private function detectTemperatureAnomaly(
        PlotRemoteSensing $current,
        Collection $historical
    ): ?array {
        if ($historical->count() < 10) {
            return null;
        }

        // Get same period last year (±2 weeks)
        $lastYearPeriod = $historical
            ->filter(fn ($d) => $d->image_date && $d->image_date->between(
                now()->subYear()->subWeeks(2),
                now()->subYear()->addWeeks(2)
            )
            );

        if ($lastYearPeriod->isEmpty()) {
            return null;
        }

        $lastYearAvgTemp = $lastYearPeriod->avg('temperature_mean');
        $currentTemp = $current->temperature_mean;
        $diff = $currentTemp - $lastYearAvgTemp;

        // Significant temperature difference
        if (abs($diff) > 5) {
            return [
                'type' => 'temperature_anomaly',
                'severity' => abs($diff) > 8 ? 'high' : 'medium',
                'title' => $diff > 0 ? 'Temperaturas Anormalmente Altas' : 'Temperaturas Anormalmente Bajas',
                'description' => sprintf(
                    'Temperatura %.1f°C %s que el año pasado (%.1f°C vs %.1f°C)',
                    abs($diff),
                    $diff > 0 ? 'más alta' : 'más baja',
                    $currentTemp,
                    $lastYearAvgTemp
                ),
                'detected_at' => now()->toIso8601String(),
                'metrics' => [
                    'current_temp' => round($currentTemp, 1),
                    'last_year_temp' => round($lastYearAvgTemp, 1),
                    'difference' => round($diff, 1),
                ],
                'icon' => $diff > 0 ? '🌡️' : '❄️',
                'color' => $diff > 0 ? 'red' : 'blue',
            ];
        }

        return null;
    }

    /**
     * Detect spatial anomalies (within-field variation)
     */
    private function detectSpatialAnomaly(PlotRemoteSensing $data): ?array
    {
        // High standard deviation = heterogeneous field
        // Could indicate drainage issues, disease patches, soil variability

        if ($data->ndvi_stddev > 0.15) {
            return [
                'type' => 'spatial_heterogeneity',
                'severity' => $data->ndvi_stddev > 0.20 ? 'high' : 'medium',
                'title' => __('Alta Variabilidad Intra-parcela'),
                'description' => sprintf(
                    'Variación NDVI dentro de la parcela: %.3f (desviación estándar)',
                    $data->ndvi_stddev
                ),
                'detected_at' => now()->toIso8601String(),
                'metrics' => [
                    'ndvi_mean' => round((float) ($data->ndvi_mean ?? 0), 3),
                    'ndvi_stddev' => round((float) $data->ndvi_stddev, 3),
                    'coefficient_variation' => round(
                        ((float) $data->ndvi_stddev / max(0.001, (float) ($data->ndvi_mean ?? 0))) * 100,
                        1
                    ),
                ],
                'probable_causes' => [
                    'Parches de enfermedad localizados',
                    'Variabilidad del suelo (fertilidad, textura)',
                    'Problemas de drenaje en zonas bajas',
                    'Riego desigual',
                    'Daño por fauna (jabalíes, conejos)',
                ],
                'recommended_actions' => [
                    '🗺️ Crear mapa de vigor (zonificación)',
                    '🔍 Inspección dirigida a zonas de bajo vigor',
                    '💧 Revisar uniformidad de riego',
                    '🧪 Análisis de suelo en zonas problemáticas',
                ],
                'icon' => '🗺️',
                'color' => 'yellow',
            ];
        }

        return null;
    }

    /**
     * Calculate overall risk level from anomalies
     */
    private function calculateRiskLevel(array $anomalies): string
    {
        if (empty($anomalies)) {
            return 'none';
        }

        $criticalCount = count(array_filter($anomalies, fn ($a) => $a['severity'] === 'critical'));
        $highCount = count(array_filter($anomalies, fn ($a) => $a['severity'] === 'high'));

        return match (true) {
            $criticalCount > 0 => 'critical',
            $highCount >= 2 => 'high',
            $highCount >= 1 => 'medium',
            default => 'low',
        };
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values): float
    {
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(
            fn ($x) => pow($x - $mean, 2),
            $values
        )) / $count;

        return sqrt($variance);
    }

    /**
     * Get human-readable metric label
     */
    private function getMetricLabel(string $metric): string
    {
        return match ($metric) {
            'ndvi_mean' => 'NDVI',
            'ndwi_mean' => __('NDWI (Agua)'),
            'evi_mean' => __('EVI (Vigor)'),
            'temperature_mean' => __('Temperatura'),
            'soil_moisture_mean' => __('Humedad del Suelo'),
            default => $metric,
        };
    }
}
