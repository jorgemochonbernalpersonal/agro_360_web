<?php

namespace App\Services\RemoteSensing;

use App\Models\Plot;
use Carbon\Carbon;

class PhenologyService
{
    /**
     * Calculate GDD (Growing Degree Days) for a plot
     * Formula: ((Max + Min) / 2) - BaseTemp
     * BaseTemp for grapes is typically 10°C
     */
    public function calculateGdd(Plot $plot): array
    {
        $weatherService = new WeatherService();
        $forecast = $weatherService->getForecast($plot, 7);
        
        // Mocking accumulated GDD for now since we don't have historical weather DB
        // In a real app, you'd query the DB for past daily temperatures
        $currentDate = Carbon::now();
        $startOfSeason = Carbon::createFromDate($currentDate->year, 4, 1); // April 1st
        
        $daysSinceStart = $currentDate->diffInDays($startOfSeason->copy()->startOfDay());
        
        // Mock accumulated (linear grow approx)
        // Avg daily GDD in season approx 6-10 units
        $accumulated = min(2000, max(0, $daysSinceStart * 8)); 
        
        $gddToday = 0;
        $gddWeekForecast = 0;

        if (!empty($forecast['forecast'])) {
            $today = $forecast['forecast'][0];
            $avg = ($today['temp_max'] + $today['temp_min']) / 2;
            $gddToday = max(0, $avg - 10);
            
            foreach ($forecast['forecast'] as $day) {
                $avg = ($day['temp_max'] + $day['temp_min']) / 2;
                $gddWeekForecast += max(0, $avg - 10);
            }
        }
        
        $stage = $this->determineStage($accumulated);
        $risks = $this->calculateRisks($plot, $accumulated);
        
        return [
            'gdd_accumulated' => round($accumulated),
            'gdd_today' => round($gddToday, 1),
            'gdd_week_forecast' => round($gddWeekForecast, 1),
            'gdd_target' => 2000, // Typical for harvest
            'stage' => $stage,
            'risks' => $risks,
            'estimated_harvest_date' => $this->estimateHarvestDate($accumulated),
            'days_to_harvest' => $this->estimateDaysToHarvest($accumulated),
        ];
    }

    /**
     * Determine phenological stage based on GDD
     */
    private function determineStage(float $gdd): array
    {
        // Wimkler Index approximation (Region III)
        return match (true) {
            $gdd < 100 => ['name' => 'Dormancia', 'icon' => '💤', 'progress' => 5],
            $gdd < 300 => ['name' => 'Brotación', 'icon' => 'sprout', 'progress' => 15],
            $gdd < 600 => ['name' => 'Floración', 'icon' => 'flower', 'progress' => 30],
            $gdd < 1200 => ['name' => 'Cuajado', 'icon' => 'green', 'progress' => 50],
            $gdd < 1500 => ['name' => 'Envero', 'icon' => 'purple', 'progress' => 75],
            $gdd < 2000 => ['name' => 'Maduración', 'icon' => 'grape', 'progress' => 90],
            default => ['name' => 'Vendimia', 'icon' => 'wine', 'progress' => 100],
        };
    }

    /**
     * Calculate Disease Risks (Mildew, Oidium) basic models
     */
    public function calculateRisks(Plot $plot, float $gddAccumulated): array
    {
        $weatherService = new WeatherService();
        $weather = $weatherService->getCurrentWeather($plot);
        $forecast = $weatherService->getForecast($plot);
        
        $risks = [];
        
        // 1. Downy Mildew (Mildiou) - Rule of 3-10
        // Temp > 10°C, Rain > 10mm, Shoots > 10cm (approx GDD > 150)
        $rainForecast = collect($forecast['forecast'] ?? [])->take(3)->sum('precipitation');
        $temp = $weather['temperature'] ?? 15;
        
        if ($gddAccumulated > 150) {
            if ($temp > 10 && ($weather['precipitation'] > 10 || $rainForecast > 10)) {
                $risks[] = [
                    'name' => 'Mildiu',
                    'level' => 'high',
                    'color' => '#ef4444',
                    'message' => 'Condiciones favorables (3-10 cumplido)',
                ];
            } elseif ($temp > 10 && $weather['humidity'] > 80) {
                 $risks[] = [
                    'name' => 'Mildiu',
                    'level' => 'medium',
                    'color' => '#eab308',
                    'message' => 'Humedad alta, vigilar lluvias',
                ];
            }
        }
        
        // 2. Powdery Mildew (Oidio)
        // Optimal: 20-27°C, Cloudy, High Humidity but NO rain
        if ($gddAccumulated > 200) {
            if ($temp >= 20 && $temp <= 27 && ($weather['humidity'] ?? 0) > 60 && ($weather['precipitation'] ?? 0) == 0) {
                $risks[] = [
                    'name' => 'Oidio',
                    'level' => 'high',
                    'color' => '#ef4444',
                    'message' => 'Temp/Humedad óptimas para esporulación',
                ];
            }
        }

        if (empty($risks)) {
            $risks[] = [
                'name' => 'General',
                'level' => 'low',
                'color' => '#22c55e',
                'message' => 'Bajo riesgo de enfermedades fúngicas',
            ];
        }
        
        return $risks;
    }
    
    private function estimateHarvestDate(float $currentGdd): string
    {
        $remaining = 2000 - $currentGdd;
        if ($remaining <= 0) return 'Ahora';
        
        // Assume avg 10 GDD/day in summer
        $days = $remaining / 10;
        return Carbon::now()->addDays($days)->format('d/m/Y');
    }
    
    private function estimateDaysToHarvest(float $currentGdd): int
    {
        $remaining = 2000 - $currentGdd;
        if ($remaining <= 0) return 0;
        return (int) ($remaining / 10);
    }
}
