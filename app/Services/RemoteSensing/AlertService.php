<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;

class AlertService
{
    // Default thresholds
    private const DEFAULT_NDVI_THRESHOLD = 0.35;
    private const DEFAULT_NDWI_THRESHOLD = -0.15;
    private const DEFAULT_TEMP_THRESHOLD = 40;

    /**
     * Check alerts for a user's plots
     */
    public function checkAlertsForUser(User $user): array
    {
        $alerts = [];

        $plots = $user->plots()->with(['latestRemoteSensing'])->get();

        foreach ($plots as $plot) {
            $plotAlerts = $this->checkPlotAlerts($plot);
            if (!empty($plotAlerts)) {
                $alerts[$plot->id] = [
                    'plot' => $plot,
                    'alerts' => $plotAlerts,
                ];
            }
        }

        return $alerts;
    }

    /**
     * Check alerts for a specific plot
     */
    public function checkPlotAlerts(Plot $plot): array
    {
        $alerts = [];
        
        $latestData = PlotRemoteSensing::where('plot_id', $plot->id)
            ->orderBy('image_date', 'desc')
            ->first();

        if (!$latestData) {
            return $alerts;
        }

        // Get thresholds (from plot settings or defaults)
        $thresholds = $this->getPlotThresholds($plot);

        // Check NDVI
        if ($latestData->ndvi_mean !== null && $latestData->ndvi_mean < $thresholds['ndvi']) {
            $alerts[] = [
                'type' => 'ndvi_low',
                'severity' => $latestData->ndvi_mean < 0.2 ? 'critical' : 'warning',
                'title' => 'NDVI Bajo',
                'message' => "El NDVI de {$plot->name} es {$this->formatValue($latestData->ndvi_mean, 3)}, por debajo del umbral ({$thresholds['ndvi']})",
                'value' => $latestData->ndvi_mean,
                'threshold' => $thresholds['ndvi'],
                'date' => $latestData->image_date->format('d/m/Y'),
            ];
        }

        // Check NDWI
        if ($latestData->ndwi_mean !== null && $latestData->ndwi_mean < $thresholds['ndwi']) {
            $alerts[] = [
                'type' => 'ndwi_low',
                'severity' => $latestData->ndwi_mean < -0.25 ? 'critical' : 'warning',
                'title' => 'Estrés Hídrico',
                'message' => "El NDWI de {$plot->name} indica estrés hídrico ({$this->formatValue($latestData->ndwi_mean, 3)})",
                'value' => $latestData->ndwi_mean,
                'threshold' => $thresholds['ndwi'],
                'date' => $latestData->image_date->format('d/m/Y'),
            ];
        }

        // Check declining trend
        if ($latestData->trend === 'decreasing' && $latestData->ndvi_change !== null && abs($latestData->ndvi_change) > 0.1) {
            $alerts[] = [
                'type' => 'trend_declining',
                'severity' => 'warning',
                'title' => 'Tendencia Decreciente',
                'message' => "El NDVI de {$plot->name} está descendiendo significativamente ({$this->formatValue($latestData->ndvi_change, 3)})",
                'value' => $latestData->ndvi_change,
                'threshold' => -0.1,
                'date' => $latestData->image_date->format('d/m/Y'),
            ];
        }

        // Check temperature if available
        if ($latestData->temperature !== null && $latestData->temperature > $thresholds['temp']) {
            $alerts[] = [
                'type' => 'temp_high',
                'severity' => $latestData->temperature > 42 ? 'critical' : 'warning',
                'title' => 'Temperatura Alta',
                'message' => "La temperatura en {$plot->name} es muy alta ({$this->formatValue($latestData->temperature, 1)}°C)",
                'value' => $latestData->temperature,
                'threshold' => $thresholds['temp'],
                'date' => $latestData->image_date->format('d/m/Y'),
            ];
        }

        return $alerts;
    }

    /**
     * Get all active alerts count for a user
     */
    public function getAlertCountForUser(User $user): int
    {
        $alerts = $this->checkAlertsForUser($user);
        return collect($alerts)->sum(fn($plotData) => count($plotData['alerts']));
    }

    /**
     * Get thresholds for a plot
     */
    private function getPlotThresholds(Plot $plot): array
    {
        // In future, this can read from plot-specific settings
        // For now, use defaults
        return [
            'ndvi' => $plot->ndvi_alert_threshold ?? self::DEFAULT_NDVI_THRESHOLD,
            'ndwi' => self::DEFAULT_NDWI_THRESHOLD,
            'temp' => self::DEFAULT_TEMP_THRESHOLD,
        ];
    }

    /**
     * Format numeric value
     */
    private function formatValue($value, int $decimals): string
    {
        return number_format($value, $decimals);
    }

    /**
     * Get severity color
     */
    public static function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'critical' => 'red',
            'warning' => 'orange',
            'info' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get severity icon
     */
    public static function getSeverityIcon(string $severity): string
    {
        return match ($severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '📋',
        };
    }
}
