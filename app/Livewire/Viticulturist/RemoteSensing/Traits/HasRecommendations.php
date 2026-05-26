<?php

namespace App\Livewire\Viticulturist\RemoteSensing\Traits;

/**
 * Agronomic recommendation generation, including predictive frost alerts.
 * Extracted from Dashboard.php to reduce its size.
 * Improvement #7: predictive frost alert checks forecast temperatures.
 */
trait HasRecommendations
{
    private function generateRecommendations(): void
    {
        $this->recommendations = [];

        if ($this->ndviData) {
            $ndvi = $this->ndviData->ndvi_mean ?? 0;
            if ($ndvi < 0.3) {
                $this->recommendations[] = [
                    'type'  => 'warning',
                    'icon'  => '🌱',
                    'title' => __('Vigor bajo detectado'),
                    'text'  => __('Revisa posibles deficiencias nutricionales o estrés.'),
                ];
            }
        }

        // Improvement #7: predictive frost alert using forecast data
        $this->addFrostRecommendation();

        $temp = $this->weather['temperature'] ?? 20;
        if ($temp > 35) {
            $this->recommendations[] = [
                'type'  => 'warning',
                'icon'  => '🔥',
                'title' => __('Estrés térmico'),
                'text'  => __('Monitoriza riego y estrés hídrico.'),
            ];
        }

        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        if ($soilMoisture < 15) {
            $this->recommendations[] = [
                'type'  => 'warning',
                'icon'  => '💧',
                'title' => __('Suelo seco'),
                'text'  => __('Humedad baja (') . round($soilMoisture) . '%). Considera riego.',
            ];
        }

        $rainDays = collect($this->forecast)->filter(fn($d) => ($d['precipitation'] ?? 0) > 5)->count();
        if ($rainDays >= 3) {
            $this->recommendations[] = [
                'type'  => 'info',
                'icon'  => '🌧️',
                'title' => __('Lluvia prevista'),
                'text'  => "$rainDays días de lluvia esta semana.",
            ];
        }

        if (empty($this->recommendations)) {
            $this->recommendations[] = [
                'type'  => 'success',
                'icon'  => '✅',
                'title' => __('Condiciones óptimas'),
                'text'  => __('Todos los indicadores normales.'),
            ];
        }
    }

    /**
     * Improvement #7: check forecast for frost risk (≤ 2°C) and escalate severity
     * when the vine is in a frost-vulnerable phenological stage (Brotacion/Floracion).
     */
    private function addFrostRecommendation(): void
    {
        $frostRiskDays = collect($this->forecast)
            ->filter(fn($d) => ($d['temp_min'] ?? 999) <= 2.0)
            ->count();

        if ($frostRiskDays <= 0) return;

        $gdd              = $this->getGrowingDegreeDays();
        $vulnerableStages = ['Brotacion', 'Floracion'];
        $isVulnerable     = in_array($gdd['stage']['name'] ?? '', $vulnerableStages);
        $stageName        = $gdd['stage']['name'] ?? '';

        $this->recommendations[] = [
            'type'  => $isVulnerable ? 'danger' : 'warning',
            'icon'  => '❄️',
            'title' => $isVulnerable ? 'RIESGO HELADA en fase crítica' : 'Riesgo de helada',
            'text'  => "Temperatura ≤2°C prevista en {$frostRiskDays} día(s). "
                . ($isVulnerable
                    ? "Viña en {$stageName} — actúa ahora."
                    : 'Monitoriza.'),
        ];
    }
}
