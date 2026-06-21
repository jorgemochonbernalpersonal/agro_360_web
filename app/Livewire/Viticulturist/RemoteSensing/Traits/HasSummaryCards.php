<?php

namespace App\Livewire\Viticulturist\RemoteSensing\Traits;

use App\Models\PlotRemoteSensing;

trait HasSummaryCards
{
    private function calculateVigorSummary(PlotRemoteSensing $data): array
    {
        $ndvi = $data->ndvi_mean ?? 0;
        $gndvi = $data->gndvi ?? 0;
        $lai = $data->lai ?? 0;

        if ($ndvi >= 0.7) {
            $status = 'excellent';
            $label = __('Excelente');
            $color = 'green';
            $icon = '✅';
        } elseif ($ndvi >= 0.5) {
            $status = 'good';
            $label = __('Bueno');
            $color = 'emerald';
            $icon = '✅';
        } elseif ($ndvi >= 0.3) {
            $status = 'moderate';
            $label = __('Moderado');
            $color = 'yellow';
            $icon = '⚠️';
        } else {
            $status = 'poor';
            $label = __('Bajo');
            $color = 'orange';
            $icon = '⚠️';
        }

        return [
            'ndvi' => $ndvi, 'gndvi' => $gndvi, 'lai' => $lai,
            'status' => $status, 'label' => $label, 'color' => $color, 'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'spectral']),
        ];
    }

    private function calculateWaterSummary(PlotRemoteSensing $data): array
    {
        $cwsi = $data->cwsi ?? null;
        $soilMoisture = $data->soil_moisture_surface_smap ?? $data->soil_moisture ?? 0;

        if ($cwsi === null) {
            return $this->getEmptyCard('water');
        }

        if ($cwsi < 0.2) {
            $status = 'excellent';
            $label = __('Sin Estrés');
            $color = 'green';
            $icon = '✅';
        } elseif ($cwsi < 0.4) {
            $status = 'good';
            $label = __('Leve');
            $color = 'yellow';
            $icon = '⚠️';
        } elseif ($cwsi < 0.6) {
            $status = 'moderate';
            $label = __('Moderado');
            $color = 'orange';
            $icon = '⚠️';
        } else {
            $status = 'critical';
            $label = __('Alto Estrés');
            $color = 'red';
            $icon = '🚨';
        }

        return [
            'cwsi' => $cwsi, 'soil_moisture' => $soilMoisture,
            'status' => $status, 'label' => $label, 'color' => $color, 'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'thermal']),
        ];
    }

    private function calculateTemperatureSummary(PlotRemoteSensing $data): array
    {
        $lstDay = $data->lst_day ?? null;
        $lstNight = $data->lst_night ?? null;
        $lstDiff = $data->lst_diff ?? null;

        if ($lstDay === null) {
            return $this->getEmptyCard('temperature');
        }

        $month = now()->month;
        $threshold = ($month >= 6 && $month <= 8) ? 42 : 38;

        if ($lstDay > $threshold + 5) {
            $status = 'critical';
            $label = __('Estrés Térmico');
            $color = 'red';
            $icon = '🔥';
        } elseif ($lstDay > $threshold) {
            $status = 'warning';
            $label = __('Calor Alto');
            $color = 'orange';
            $icon = '⚠️';
        } elseif ($lstNight !== null && $lstNight < 3 && $month >= 3 && $month <= 5) {
            $status = 'warning';
            $label = __('Riesgo Helada');
            $color = 'blue';
            $icon = '❄️';
        } else {
            $status = 'normal';
            $label = __('Normal');
            $color = 'green';
            $icon = '✅';
        }

        return [
            'lst_day' => $lstDay, 'lst_night' => $lstNight, 'lst_diff' => $lstDiff,
            'status' => $status, 'label' => $label, 'color' => $color, 'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'thermal']),
        ];
    }

    private function calculateHarvestSummary(PlotRemoteSensing $data): array
    {
        $lai = $data->lai ?? null;

        if ($lai === null) {
            return $this->getEmptyCard('harvest');
        }

        $baseYield = 6.5; // tons/ha for red wine
        $laiFactor = min(1.5, $lai / 2.5);
        $yieldPerHa = $baseYield * $laiFactor;
        $areaHa = $this->selectedPlot->area ?? 1;
        $totalYield = $yieldPerHa * $areaHa;

        if ($lai >= 1.5 && $lai <= 3.5) {
            $confidence = 'high';
            $confidenceLabel = __('Alta');
            $color = 'green';
        } elseif ($lai >= 1.0 && $lai <= 4.5) {
            $confidence = 'medium';
            $confidenceLabel = __('Media');
            $color = 'yellow';
        } else {
            $confidence = 'low';
            $confidenceLabel = __('Baja');
            $color = 'orange';
        }

        return [
            'lai' => $lai, 'yield_per_ha' => $yieldPerHa, 'total_yield' => $totalYield,
            'confidence' => $confidence, 'confidence_label' => $confidenceLabel, 'color' => $color,
            'icon' => '🍇',
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'lai-official']),
        ];
    }

    private function calculateNutritionSummary(PlotRemoteSensing $data): array
    {
        $gndvi = $data->gndvi ?? null;
        $chlorophyll = $data->chlorophyll_content ?? null;

        if ($gndvi === null) {
            return $this->getEmptyCard('nutrition');
        }

        if ($gndvi >= 0.6) {
            $status = 'optimal';
            $label = __('Óptimo');
            $color = 'green';
            $icon = '✅';
        } elseif ($gndvi >= 0.5) {
            $status = 'good';
            $label = __('Bueno');
            $color = 'emerald';
            $icon = '✅';
        } elseif ($gndvi >= 0.3) {
            $status = 'low';
            $label = __('Bajo N');
            $color = 'yellow';
            $icon = '⚠️';
        } else {
            $status = 'deficient';
            $label = __('Deficiente');
            $color = 'red';
            $icon = '🚨';
        }

        return [
            'gndvi' => $gndvi, 'chlorophyll' => $chlorophyll,
            'status' => $status, 'label' => $label, 'color' => $color, 'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'spectral']),
        ];
    }

    private function calculateAlerts(PlotRemoteSensing $data): array
    {
        $alerts = [];
        $critical = 0;
        $warnings = 0;

        if (isset($data->cwsi) && $data->cwsi > 0.6) {
            $alerts[] = ['type' => 'critical', 'message' => __('Estrés hídrico alto')];
            $critical++;
        } elseif (isset($data->cwsi) && $data->cwsi > 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => __('Estrés hídrico moderado')];
            $warnings++;
        }

        if (isset($data->gndvi) && $data->gndvi < 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => __('Nivel bajo de nitrógeno')];
            $warnings++;
        }

        if ($data->anomaly_detected) {
            $alerts[] = ['type' => 'critical', 'message' => $data->anomaly_type ?? 'Anomalía detectada'];
            $critical++;
        }

        if (isset($data->lst_day) && $data->lst_day > 40) {
            $alerts[] = ['type' => 'critical', 'message' => __('Temperatura superficial muy alta')];
            $critical++;
        }

        return [
            'total' => count($alerts), 'critical' => $critical, 'warnings' => $warnings, 'list' => $alerts,
            'color' => $critical > 0 ? 'red' : ($warnings > 0 ? 'yellow' : 'green'),
            'icon' => $critical > 0 ? '🚨' : ($warnings > 0 ? '⚠️' : '✅'),
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'satellite']),
        ];
    }

    private function getNdviColor(?float $ndvi): array
    {
        if ($ndvi === null) {
            return ['fill' => 'rgba(156, 163, 175, 0.5)', 'line' => '#6b7280'];
        }

        return match (true) {
            $ndvi >= 0.7 => ['fill' => 'rgba(34, 197, 94, 0.6)',  'line' => '#16a34a'],
            $ndvi >= 0.5 => ['fill' => 'rgba(52, 211, 153, 0.6)', 'line' => '#10b981'],
            $ndvi >= 0.3 => ['fill' => 'rgba(250, 204, 21, 0.6)', 'line' => '#ca8a04'],
            $ndvi >= 0.15 => ['fill' => 'rgba(251, 146, 60, 0.6)', 'line' => '#ea580c'],
            default => ['fill' => 'rgba(239, 68, 68, 0.6)',  'line' => '#dc2626'],
        };
    }

    private function getEmptyCard(string $type): array
    {
        $base = [
            'status' => 'no_data', 'label' => __('Sin Datos'), 'color' => 'gray', 'icon' => '❓',
            'detail_route' => route('remote-sensing.advanced'),
        ];

        match ($type) {
            'vigor' => $base += ['ndvi' => 0, 'gndvi' => null, 'lai' => null],
            'water' => $base += ['cwsi' => null, 'soil_moisture' => null],
            'temperature' => $base += ['lst_day' => null, 'lst_night' => null, 'lst_diff' => null],
            'harvest' => $base += ['lai' => null, 'yield_per_ha' => null, 'total_yield' => null, 'confidence' => 'low', 'confidence_label' => 'Sin datos'],
            'nutrition' => $base += ['gndvi' => null, 'chlorophyll' => null],
            default => null,
        };

        return $base;
    }

    private function getEmptySummary(): array
    {
        return [
            'vigor' => $this->getEmptyCard('vigor'),
            'water' => $this->getEmptyCard('water'),
            'temperature' => $this->getEmptyCard('temperature'),
            'harvest' => $this->getEmptyCard('harvest'),
            'nutrition' => $this->getEmptyCard('nutrition'),
            'alerts' => ['total' => 0, 'critical' => 0, 'warnings' => 0, 'list' => [], 'color' => 'gray', 'icon' => '❓'],
            'last_update' => __('Nunca'),
            'satellite' => __('N/A'),
            'is_estimated' => false,
        ];
    }
}
