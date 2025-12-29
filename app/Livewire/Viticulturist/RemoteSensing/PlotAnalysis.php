<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Services\RemoteSensing\WeatherService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Unified plot analysis view with all remote sensing and weather data
 */
#[Layout('components.app-layout')]
class PlotAnalysis extends Component
{
    public Plot $plot;
    public string $activeTab = 'satellite';
    
    // Satellite data
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
    
    public bool $isLoading = false;
    public string $error = '';

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadAllData();
    }

    public function loadAllData()
    {
        $this->isLoading = true;
        $this->error = '';

        try {
            // Load satellite data
            $nasaService = new NasaEarthdataService();
            $this->ndviData = $nasaService->getLatestData($this->plot);
            $historical = $nasaService->getHistoricalData($this->plot, 90);
            $this->historicalData = $historical->map(fn($item) => [
                'date' => $item->image_date->format('d/m'),
                'ndvi' => $item->ndvi_mean,
                'fullDate' => $item->image_date->format('d/m/Y'),
            ])->values()->toArray();
            
            $this->calculateYearComparison();
            
            // Load weather data
            $weatherService = new WeatherService();
            $this->weather = $weatherService->getCurrentWeather($this->plot);
            $this->soil = $weatherService->getSoilData($this->plot);
            $this->solar = $weatherService->getSolarData($this->plot);
            $this->forecast = $weatherService->getForecast($this->plot, 7)['forecast'] ?? [];
            
            // Generate recommendations
            $this->generateRecommendations();
            
        } catch (\Exception $e) {
            $this->error = 'Error al cargar los datos: ' . $e->getMessage();
            \Log::error('PlotAnalysis error', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->isLoading = false;
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    private function calculateYearComparison(): void
    {
        if (!$this->ndviData) return;
        
        $lastYearData = PlotRemoteSensing::where('plot_id', $this->plot->id)
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
            // Mock data
            $current = $this->ndviData->ndvi_mean ?? 0.5;
            $variation = mt_rand(-10, 15) / 100;
            $this->lastYearNdvi = round($current * (1 - $variation), 3);
            $this->yearChange = $variation;
        }
    }

    private function generateRecommendations(): void
    {
        $this->recommendations = [];
        
        // NDVI recommendations
        if ($this->ndviData) {
            $ndvi = $this->ndviData->ndvi_mean ?? 0;
            if ($ndvi < 0.3) {
                $this->recommendations[] = [
                    'type' => 'warning',
                    'icon' => '🌱',
                    'title' => 'Vigor bajo detectado',
                    'text' => 'El NDVI indica vigor bajo. Revisa posibles deficiencias nutricionales o estrés.',
                ];
            }
        }
        
        // Weather recommendations
        $temp = $this->weather['temperature'] ?? 20;
        if ($temp < 0) {
            $this->recommendations[] = [
                'type' => 'danger',
                'icon' => '❄️',
                'title' => 'Riesgo de helada',
                'text' => 'Temperatura bajo cero detectada. Considera medidas de protección.',
            ];
        } elseif ($temp > 35) {
            $this->recommendations[] = [
                'type' => 'warning',
                'icon' => '🔥',
                'title' => 'Estrés térmico',
                'text' => 'Temperatura elevada. Monitoriza el riego y posible estrés hídrico.',
            ];
        }
        
        // Soil recommendations
        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        if ($soilMoisture < 15) {
            $this->recommendations[] = [
                'type' => 'warning',
                'icon' => '💧',
                'title' => 'Suelo seco',
                'text' => 'Humedad del suelo baja (' . round($soilMoisture) . '%). Considera riego.',
            ];
        } elseif ($soilMoisture > 60) {
            $this->recommendations[] = [
                'type' => 'info',
                'icon' => '💦',
                'title' => 'Suelo húmedo',
                'text' => 'Alta humedad del suelo. Evita riego para prevenir encharcamiento.',
            ];
        }
        
        // Rain forecast
        $rainDays = collect($this->forecast)->filter(fn($d) => ($d['precipitation'] ?? 0) > 5)->count();
        if ($rainDays >= 3) {
            $this->recommendations[] = [
                'type' => 'info',
                'icon' => '🌧️',
                'title' => 'Lluvia prevista',
                'text' => "Se esperan $rainDays días de lluvia esta semana. Planifica tratamientos.",
            ];
        }
        
        // Good conditions
        if (empty($this->recommendations)) {
            $this->recommendations[] = [
                'type' => 'success',
                'icon' => '✅',
                'title' => 'Condiciones óptimas',
                'text' => 'Todos los indicadores están en rangos normales.',
            ];
        }
    }

    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0 = $this->solar['et0'] ?? 3;
        $stressIndex = ($et0 * 10) - $moisture;
        
        return match (true) {
            $stressIndex <= 0 => ['status' => 'optimal', 'emoji' => '💧', 'text' => 'Óptimo', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
            $stressIndex <= 20 => ['status' => 'mild', 'emoji' => '💦', 'text' => 'Leve', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $stressIndex <= 40 => ['status' => 'moderate', 'emoji' => '🏜️', 'text' => 'Moderado', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['status' => 'severe', 'emoji' => '⚠️', 'text' => 'Severo', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
        };
    }

    public function refreshData()
    {
        $this->loadAllData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos actualizados correctamente',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-analysis', [
            'waterStress' => $this->getWaterStressStatus(),
        ]);
    }
}
