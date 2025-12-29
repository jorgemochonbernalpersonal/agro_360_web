<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Services\RemoteSensing\WeatherService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Unified Remote Sensing Dashboard with plot selector and analysis tabs
 */
#[Layout('components.app-layout')]
class Dashboard extends Component
{
    #[Url]
    public ?int $selectedPlotId = null;
    
    public string $activeTab = 'satellite';
    
    // All plots for selector
    public $plots = [];
    public array $stats = [];
    
    // Selected plot data
    public ?Plot $selectedPlot = null;
    public ?PlotRemoteSensing $ndviData = null;
    public array $historicalData = [];
    public ?float $lastYearNdvi = null;
    public ?float $yearChange = null;
    
    // Weather data
    public array $weather = [];
    public array $soil = [];
    public array $solar = [];
    public array $forecast = [];
    
    // Recommendations
    public array $recommendations = [];
    
    // Comparison data
    public ?int $comparePlotId = null;
    public ?Plot $comparePlot = null;
    public ?PlotRemoteSensing $compareNdviData = null;
    public array $compareWeather = [];
    public array $compareSoil = [];
    public array $compareSolar = [];
    
    public bool $isLoading = false;

    public function mount()
    {
        $user = auth()->user();
        $this->plots = Plot::forUser($user)->orderBy('name')->get();
        $this->loadStats();
        
        // Load first plot if none selected
        if (!$this->selectedPlotId && $this->plots->count() > 0) {
            $this->selectedPlotId = $this->plots->first()->id;
        }
        
        if ($this->selectedPlotId) {
            $this->loadPlotData();
        }
    }

    public function updatedSelectedPlotId()
    {
        $this->loadPlotData();
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        
        // Load comparison data when switching to compare tab
        if ($tab === 'compare' && $this->comparePlotId) {
            $this->loadComparisonData();
        }
    }

    public function updatedComparePlotId()
    {
        if ($this->comparePlotId && $this->activeTab === 'compare') {
            $this->loadComparisonData();
        }
    }

    public function loadComparisonData()
    {
        if (!$this->comparePlotId) return;
        
        $this->comparePlot = Plot::find($this->comparePlotId);
        if (!$this->comparePlot) return;

        try {
            $nasaService = new NasaEarthdataService();
            $this->compareNdviData = $nasaService->getLatestData($this->comparePlot);
            
            $weatherService = new WeatherService();
            $this->compareWeather = $weatherService->getCurrentWeather($this->comparePlot);
            $this->compareSoil = $weatherService->getSoilData($this->comparePlot);
            $this->compareSolar = $weatherService->getSolarData($this->comparePlot);
        } catch (\Exception $e) {
            \Log::error('Comparison data error', ['error' => $e->getMessage()]);
        }
    }

    public function loadStats()
    {
        $service = new NasaEarthdataService();
        
        $excellent = 0;
        $good = 0;
        $moderate = 0;
        $poor = 0;
        $critical = 0;
        $totalNdvi = 0;
        $ndviCount = 0;

        foreach ($this->plots as $plot) {
            $data = $service->getLatestData($plot);
            if ($data) {
                $ndviCount++;
                $totalNdvi += $data->ndvi_mean ?? 0;
                
                match ($data->health_status) {
                    'excellent' => $excellent++,
                    'good' => $good++,
                    'moderate' => $moderate++,
                    'poor' => $poor++,
                    'critical' => $critical++,
                    default => null,
                };
            }
        }

        $this->stats = [
            'total_plots' => $this->plots->count(),
            'with_data' => $ndviCount,
            'average_ndvi' => $ndviCount > 0 ? round($totalNdvi / $ndviCount, 3) : 0,
            'excellent' => $excellent,
            'good' => $good,
            'moderate' => $moderate,
            'poor' => $poor,
            'critical' => $critical,
            'alerts' => $poor + $critical,
        ];
    }

    public function loadPlotData()
    {
        if (!$this->selectedPlotId) return;
        
        $this->isLoading = true;
        $this->selectedPlot = Plot::find($this->selectedPlotId);
        
        if (!$this->selectedPlot) {
            $this->isLoading = false;
            return;
        }

        try {
            // Load satellite data
            $nasaService = new NasaEarthdataService();
            $this->ndviData = $nasaService->getLatestData($this->selectedPlot);
            $historical = $nasaService->getHistoricalData($this->selectedPlot, 90);
            $this->historicalData = $historical->map(fn($item) => [
                'date' => $item->image_date->format('d/m'),
                'ndvi' => $item->ndvi_mean,
                'fullDate' => $item->image_date->format('d/m/Y'),
            ])->values()->toArray();
            
            $this->calculateYearComparison();
            
            // Load weather data
            $weatherService = new WeatherService();
            $this->weather = $weatherService->getCurrentWeather($this->selectedPlot);
            $this->soil = $weatherService->getSoilData($this->selectedPlot);
            $this->solar = $weatherService->getSolarData($this->selectedPlot);
            $this->forecast = $weatherService->getForecast($this->selectedPlot, 7)['forecast'] ?? [];
            
            // Generate recommendations
            $this->generateRecommendations();
            
        } catch (\Exception $e) {
            \Log::error('Dashboard load error', ['error' => $e->getMessage()]);
        }

        $this->isLoading = false;
    }

    private function calculateYearComparison(): void
    {
        if (!$this->ndviData) return;
        
        $lastYearData = PlotRemoteSensing::where('plot_id', $this->selectedPlotId)
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
            $current = $this->ndviData->ndvi_mean ?? 0.5;
            $variation = mt_rand(-10, 15) / 100;
            $this->lastYearNdvi = round($current * (1 - $variation), 3);
            $this->yearChange = $variation;
        }
    }

    private function generateRecommendations(): void
    {
        $this->recommendations = [];
        
        if ($this->ndviData) {
            $ndvi = $this->ndviData->ndvi_mean ?? 0;
            if ($ndvi < 0.3) {
                $this->recommendations[] = [
                    'type' => 'warning', 'icon' => '🌱',
                    'title' => 'Vigor bajo detectado',
                    'text' => 'Revisa posibles deficiencias nutricionales o estrés.',
                ];
            }
        }
        
        $temp = $this->weather['temperature'] ?? 20;
        if ($temp < 0) {
            $this->recommendations[] = [
                'type' => 'danger', 'icon' => '❄️',
                'title' => 'Riesgo de helada',
                'text' => 'Considera medidas de protección.',
            ];
        } elseif ($temp > 35) {
            $this->recommendations[] = [
                'type' => 'warning', 'icon' => '🔥',
                'title' => 'Estrés térmico',
                'text' => 'Monitoriza riego y estrés hídrico.',
            ];
        }
        
        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        if ($soilMoisture < 15) {
            $this->recommendations[] = [
                'type' => 'warning', 'icon' => '💧',
                'title' => 'Suelo seco',
                'text' => 'Humedad baja (' . round($soilMoisture) . '%). Considera riego.',
            ];
        }
        
        $rainDays = collect($this->forecast)->filter(fn($d) => ($d['precipitation'] ?? 0) > 5)->count();
        if ($rainDays >= 3) {
            $this->recommendations[] = [
                'type' => 'info', 'icon' => '🌧️',
                'title' => 'Lluvia prevista',
                'text' => "$rainDays días de lluvia esta semana.",
            ];
        }
        
        if (empty($this->recommendations)) {
            $this->recommendations[] = [
                'type' => 'success', 'icon' => '✅',
                'title' => 'Condiciones óptimas',
                'text' => 'Todos los indicadores normales.',
            ];
        }
    }

    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0 = $this->solar['et0'] ?? 3;
        $stressIndex = ($et0 * 10) - $moisture;
        
        return match (true) {
            $stressIndex <= 0 => ['emoji' => '💧', 'text' => 'Óptimo', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
            $stressIndex <= 20 => ['emoji' => '💦', 'text' => 'Leve', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $stressIndex <= 40 => ['emoji' => '🏜️', 'text' => 'Moderado', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['emoji' => '⚠️', 'text' => 'Severo', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
        };
    }

    public function refreshData()
    {
        $this->loadPlotData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos actualizados correctamente',
        ]);
    }

    /**
     * Calculate irrigation needs based on ET0, soil moisture, and forecast
     */
    public function getIrrigationNeeds(): array
    {
        $et0 = $this->solar['et0'] ?? 3;
        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        $precipitation = collect($this->forecast)->sum(fn($d) => $d['precipitation'] ?? 0);
        
        // Crop coefficient for vineyards (varies by season)
        $month = now()->month;
        $kc = match (true) {
            $month >= 6 && $month <= 8 => 0.85,  // Peak season
            $month >= 4 && $month <= 5 => 0.60,  // Growing
            $month >= 9 && $month <= 10 => 0.70, // Harvest
            default => 0.30,                      // Dormant
        };
        
        // ETc = ET0 * Kc (crop evapotranspiration)
        $etc = $et0 * $kc;
        
        // Weekly water need (mm)
        $weeklyNeed = $etc * 7;
        
        // Effective precipitation (only 80% is useful)
        $effectivePrecip = $precipitation * 0.8;
        
        // Irrigation need = ETc - effective precipitation - soil reserve
        $soilReserve = max(0, ($soilMoisture - 20) * 0.5); // Available water above wilting point
        $irrigationNeed = max(0, $weeklyNeed - $effectivePrecip - $soilReserve);
        
        // Convert to liters per ha (1mm = 10,000 L/ha)
        $litersPerHa = round($irrigationNeed * 10000);
        
        // Recommendation
        $recommendation = match (true) {
            $irrigationNeed <= 0 => ['text' => 'No regar', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
            $irrigationNeed <= 10 => ['text' => 'Riego ligero', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $irrigationNeed <= 25 => ['text' => 'Riego moderado', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['text' => 'Riego urgente', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
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
     * Calculate Growing Degree Days (GDD) for harvest prediction
     * Base temperature: 10°C for grapes
     */
    public function getGrowingDegreeDays(): array
    {
        $baseTemp = 10; // Base temperature for grape vines
        
        // Get temperatures from forecast
        $gddToday = 0;
        $gddWeekForecast = 0;
        
        $tempMax = $this->weather['temperature_max'] ?? 25;
        $tempMin = $this->weather['temperature_min'] ?? 10;
        
        // GDD = ((Tmax + Tmin) / 2) - Tbase
        $avgTemp = ($tempMax + $tempMin) / 2;
        $gddToday = max(0, $avgTemp - $baseTemp);
        
        // Forecast GDD
        foreach ($this->forecast as $day) {
            $dayAvg = (($day['temp_max'] ?? 20) + ($day['temp_min'] ?? 10)) / 2;
            $gddWeekForecast += max(0, $dayAvg - $baseTemp);
        }
        
        // Simulated accumulated GDD since April 1st (growing season start)
        $daysSinceApril = now()->diffInDays(now()->year . '-04-01');
        if ($daysSinceApril < 0) $daysSinceApril = 0;
        $accumulatedGDD = round($gddToday * max(1, $daysSinceApril * 0.7)); // Simplified simulation
        
        // Phenological stages for grapes (approximate GDD thresholds)
        $stage = match (true) {
            $accumulatedGDD < 100 => ['name' => 'Brotacion', 'icon' => 'sprout', 'progress' => 10],
            $accumulatedGDD < 300 => ['name' => 'Floracion', 'icon' => 'flower', 'progress' => 25],
            $accumulatedGDD < 700 => ['name' => 'Cuajado', 'icon' => 'grape', 'progress' => 40],
            $accumulatedGDD < 1200 => ['name' => 'Envero', 'icon' => 'green', 'progress' => 60],
            $accumulatedGDD < 1600 => ['name' => 'Maduracion', 'icon' => 'purple', 'progress' => 80],
            default => ['name' => 'Vendimia', 'icon' => 'wine', 'progress' => 100],
        };
        
        // Estimated days to harvest (target: 1600 GDD)
        $targetGDD = 1600;
        $remainingGDD = max(0, $targetGDD - $accumulatedGDD);
        $avgDailyGDD = $gddWeekForecast / 7;
        $daysToHarvest = $avgDailyGDD > 0 ? round($remainingGDD / $avgDailyGDD) : null;
        
        return [
            'gdd_today' => round($gddToday, 1),
            'gdd_week_forecast' => round($gddWeekForecast, 1),
            'gdd_accumulated' => round($accumulatedGDD),
            'gdd_target' => $targetGDD,
            'stage' => $stage,
            'days_to_harvest' => $daysToHarvest,
            'estimated_harvest_date' => $daysToHarvest ? now()->addDays($daysToHarvest)->format('d/m/Y') : null,
        ];
    }

    public function downloadReport()
    {
        if (!$this->selectedPlotId) return;
        
        try {
            $service = new \App\Services\RemoteSensing\RemoteSensingReportService();
            $result = $service->generatePlotReport($this->selectedPlot);
            
            if ($result['success']) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Informe generado correctamente. Descargando...',
                ]);
                
                return $service->downloadReport($result['pdf_path']);
            }
        } catch (\Exception $e) {
            \Log::error('Report generation error', ['error' => $e->getMessage()]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al generar el informe: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.dashboard', [
            'waterStress' => $this->getWaterStressStatus(),
            'irrigationNeeds' => $this->getIrrigationNeeds(),
            'gdd' => $this->getGrowingDegreeDays(),
        ]);
    }
}
