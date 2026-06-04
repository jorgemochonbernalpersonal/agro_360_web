<?php

namespace App\Livewire\Viticulturist\RemoteSensing\Traits;

use App\Models\PlotRemoteSensing;

/**
 * Agronomic calculations: irrigation needs, GDD, water stress, year comparison.
 * Extracted from Dashboard.php to reduce its size.
 */
trait HasAgriculturalCalculations
{
    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0 = $this->solar['et0'] ?? 3;
        $stressIndex = ($et0 * 10) - $moisture;

        return match (true) {
            $stressIndex <= 0 => ['emoji' => '💧', 'text' => __('Óptimo'),   'color' => 'text-green-600',  'bg' => 'bg-green-100'],
            $stressIndex <= 20 => ['emoji' => '💦', 'text' => __('Leve'),     'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $stressIndex <= 40 => ['emoji' => '🏜️', 'text' => __('Moderado'), 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['emoji' => '⚠️', 'text' => __('Severo'),   'color' => 'text-red-600',    'bg' => 'bg-red-100'],
        };
    }

    /**
     * Calculate irrigation needs based on ET0, soil moisture, and forecast.
     */
    public function getIrrigationNeeds(): array
    {
        $et0 = $this->solar['et0'] ?? 3;
        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        $precipitation = collect($this->forecast)->sum(fn ($d) => $d['precipitation'] ?? 0);

        $month = now()->month;
        $kc = match (true) {
            $month >= 6 && $month <= 8 => 0.85,
            $month >= 4 && $month <= 5 => 0.60,
            $month >= 9 && $month <= 10 => 0.70,
            default => 0.30,
        };

        $etc = $et0 * $kc;
        $weeklyNeed = $etc * 7;
        $effectivePrecip = $precipitation * 0.8;
        $soilReserve = max(0, ($soilMoisture - 20) * 0.5);
        $irrigationNeed = max(0, $weeklyNeed - $effectivePrecip - $soilReserve);
        $litersPerHa = round($irrigationNeed * 10000);

        $recommendation = match (true) {
            $irrigationNeed <= 0 => ['text' => __('No regar'),       'color' => 'text-green-600',  'bg' => 'bg-green-100'],
            $irrigationNeed <= 10 => ['text' => __('Riego ligero'),   'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $irrigationNeed <= 25 => ['text' => __('Riego moderado'), 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['text' => __('Riego urgente'),  'color' => 'text-red-600',    'bg' => 'bg-red-100'],
        };

        return [
            'et0' => round($et0, 2),
            'kc' => $kc,
            'etc' => round($etc, 2),
            'weekly_need_mm' => round($weeklyNeed, 1),
            'expected_rain_mm' => round($effectivePrecip, 1),
            'soil_reserve_mm' => round($soilReserve, 1),
            'irrigation_need_mm' => round($irrigationNeed, 1),
            'liters_per_ha' => $litersPerHa,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Calculate Growing Degree Days (GDD) for harvest prediction.
     * Improvement #9: GDD accumulation starts from last real budbreak observation
     * in the phenology module, falling back to April 1st if none found.
     */
    public function getGrowingDegreeDays(): array
    {
        $baseTemp = 10; // Base temperature for grape vines

        $tempMax = $this->weather['temperature_max'] ?? 25;
        $tempMin = $this->weather['temperature_min'] ?? 10;
        $avgTemp = ($tempMax + $tempMin) / 2;
        $gddToday = max(0, $avgTemp - $baseTemp);

        $gddWeekForecast = 0;
        foreach ($this->forecast as $day) {
            $dayAvg = (($day['temp_max'] ?? 20) + ($day['temp_min'] ?? 10)) / 2;
            $gddWeekForecast += max(0, $dayAvg - $baseTemp);
        }

        // Improvement #9: use last budbreak observation date as GDD start
        $gddStartDate = $this->resolveGddStartDate();
        $daysSinceStart = max(0, now()->diffInDays($gddStartDate, false) * -1);
        if ($daysSinceStart < 0) {
            $daysSinceStart = 0;
        }

        $accumulatedGDD = round($gddToday * max(1, $daysSinceStart * 0.7));

        $stage = match (true) {
            $accumulatedGDD < 100 => ['name' => __('Brotacion'),  'icon' => 'sprout', 'progress' => 10],
            $accumulatedGDD < 300 => ['name' => __('Floracion'),  'icon' => 'flower', 'progress' => 25],
            $accumulatedGDD < 700 => ['name' => __('Cuajado'),    'icon' => 'grape',  'progress' => 40],
            $accumulatedGDD < 1200 => ['name' => __('Envero'),     'icon' => 'green',  'progress' => 60],
            $accumulatedGDD < 1600 => ['name' => __('Maduracion'), 'icon' => 'purple', 'progress' => 80],
            default => ['name' => __('Vendimia'),   'icon' => 'wine',   'progress' => 100],
        };

        $targetGDD = 1600;
        $remainingGDD = max(0, $targetGDD - $accumulatedGDD);
        $avgDailyGDD = $gddWeekForecast / 7;
        $daysToHarvest = $avgDailyGDD > 0 ? round($remainingGDD / $avgDailyGDD) : null;

        return [
            'gdd_today' => round($gddToday, 1),
            'gdd_week_forecast' => round($gddWeekForecast, 1),
            'gdd_accumulated' => round($accumulatedGDD),
            'gdd_target' => $targetGDD,
            'gdd_start_date' => $gddStartDate->format('d/m/Y'),
            'stage' => $stage,
            'days_to_harvest' => $daysToHarvest,
            'estimated_harvest_date' => $daysToHarvest ? now()->addDays($daysToHarvest)->format('d/m/Y') : null,
        ];
    }

    /**
     * Improvement #10: Replace random mock for year comparison with real data.
     * If no data exists for the same month last year, both values stay null.
     */
    private function calculateYearComparison(): void
    {
        if (! $this->ndviData) {
            return;
        }

        $lastYearData = PlotRemoteSensing::where('plot_id', $this->selectedPlot->id)
            ->whereMonth('image_date', now()->month)
            ->whereYear('image_date', now()->year - 1)
            ->first();

        if ($lastYearData) {
            $this->lastYearNdvi = $lastYearData->ndvi_mean;
            $current = $this->ndviData->ndvi_mean ?? 0;
            if ($this->lastYearNdvi > 0) {
                $this->yearChange = ($current - $this->lastYearNdvi) / $this->lastYearNdvi;
            }
        } else {
            // No data for last year — do NOT fabricate random values
            $this->lastYearNdvi = null;
            $this->yearChange = null;
        }
    }

    /**
     * Resolve the GDD accumulation start date.
     * Uses the most recent 'budbreak' phenology observation for the current plot,
     * falling back to April 1st of the current growing year.
     */
    private function resolveGddStartDate(): \Carbon\Carbon
    {
        if ($this->selectedPlot) {
            $lastBudbreak = \App\Models\PhenologyObservation::whereHas(
                'plotPlanting',
                fn ($q) => $q->where('plot_id', $this->selectedPlot->id)
            )
                ->where('event', 'budbreak')
                ->orderBy('obs_date', 'desc')
                ->value('obs_date');

            if ($lastBudbreak) {
                return \Carbon\Carbon::parse($lastBudbreak);
            }
        }

        // Fallback: April 1st of current growing year
        $year = now()->month < 4 ? now()->year - 1 : now()->year;

        return \Carbon\Carbon::create($year, 4, 1);
    }
}
