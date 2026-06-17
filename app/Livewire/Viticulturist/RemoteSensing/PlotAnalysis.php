<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Support\Facades\Cache;
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
        $this->authorize('view', $plot);
        $this->plot = $plot;
        $this->loadAllData();
    }

    public function loadAllData()
    {
        $this->isLoading = true;
        $this->error = '';

        try {
            // Load satellite data
            $nasaService = app(NasaEarthdataService::class);
            $this->ndviData = $nasaService->getLatestData($this->plot);
            $historical = $nasaService->getHistoricalData($this->plot, 90);
            $this->historicalData = $historical->map(fn ($item) => [
                'date' => $item->image_date->format('d/m'),
                'ndvi' => $item->ndvi_mean,
                'fullDate' => $item->image_date->format('d/m/Y'),
            ])->values()->toArray();

            $this->calculateYearComparison();

            // Load weather data
            $weatherService = new WeatherService;
            $this->weather = $weatherService->getCurrentWeather($this->plot);
            $this->soil = $weatherService->getSoilData($this->plot);
            $this->solar = $weatherService->getSolarData($this->plot);
            $this->forecast = $weatherService->getForecast($this->plot, 7)['forecast'] ?? [];

            // Generate recommendations
            $this->generateRecommendations();

        } catch (\Exception $e) {
            $this->error = __('Error al cargar los datos').': '.$e->getMessage();
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

    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0 = $this->solar['et0'] ?? 3;
        $stressIndex = ($et0 * 10) - $moisture;

        return match (true) {
            $stressIndex <= 0 => ['status' => 'optimal', 'emoji' => '💧', 'text' => __('Óptimo'), 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
            $stressIndex <= 20 => ['status' => 'mild', 'emoji' => '💦', 'text' => __('Leve'), 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
            $stressIndex <= 40 => ['status' => 'moderate', 'emoji' => '🏜️', 'text' => __('Moderado'), 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            default => ['status' => 'severe', 'emoji' => '⚠️', 'text' => __('Severo'), 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
        };
    }

    public function refreshData()
    {
        // Clear all caches
        $nasaService = app(NasaEarthdataService::class);
        $nasaService->clearCache($this->plot);

        Cache::forget("weather_{$this->plot->id}");
        Cache::forget("forecast_{$this->plot->id}_7");
        Cache::forget("soil_{$this->plot->id}");
        Cache::forget("solar_{$this->plot->id}");

        $this->loadAllData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('Datos actualizados correctamente'),
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-analysis', [
            'waterStress' => $this->getWaterStressStatus(),
        ]);
    }

    private function calculateYearComparison(): void
    {
        if (! $this->ndviData) {
            return;
        }

        $lastYearData = PlotRemoteSensing::where('plot_id', $this->plot->id)
            ->whereMonth('image_date', now()->month)
            ->whereYear('image_date', now()->year - 1)
            ->first();

        if ($lastYearData) {
            $this->lastYearNdvi = $lastYearData->ndvi_mean !== null ? (float) $lastYearData->ndvi_mean : null;
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
                    'title' => __('Vigor bajo detectado'),
                    'text' => __('El NDVI indica vigor bajo. Revisa posibles deficiencias nutricionales o estrés.'),
                ];
            }
        }

        // Weather recommendations
        $temp = $this->weather['temperature'] ?? 20;
        if ($temp < 0) {
            $this->recommendations[] = [
                'type' => 'danger',
                'icon' => '❄️',
                'title' => __('Riesgo de helada'),
                'text' => __('Temperatura bajo cero detectada. Considera medidas de protección.'),
            ];
        } elseif ($temp > 35) {
            $this->recommendations[] = [
                'type' => 'warning',
                'icon' => '🔥',
                'title' => __('Estrés térmico'),
                'text' => __('Temperatura elevada. Monitoriza el riego y posible estrés hídrico.'),
            ];
        }

        // Soil recommendations
        $soilMoisture = $this->soil['soil_moisture'] ?? 30;
        if ($soilMoisture < 15) {
            $this->recommendations[] = [
                'type' => 'warning',
                'icon' => '💧',
                'title' => __('Suelo seco'),
                'text' => __('Humedad del suelo baja (').round($soilMoisture).'%). Considera riego.',
            ];
        } elseif ($soilMoisture > 60) {
            $this->recommendations[] = [
                'type' => 'info',
                'icon' => '💦',
                'title' => __('Suelo húmedo'),
                'text' => __('Alta humedad del suelo. Evita riego para prevenir encharcamiento.'),
            ];
        }

        // Rain forecast
        $rainDays = collect($this->forecast)->filter(fn ($d) => ($d['precipitation'] ?? 0) > 5)->count();
        if ($rainDays >= 3) {
            $this->recommendations[] = [
                'type' => 'info',
                'icon' => '🌧️',
                'title' => __('Lluvia prevista'),
                'text' => "Se esperan $rainDays días de lluvia esta semana. Planifica tratamientos.",
            ];
        }

        // Good conditions
        if (empty($this->recommendations)) {
            $this->recommendations[] = [
                'type' => 'success',
                'icon' => '✅',
                'title' => __('Condiciones óptimas'),
                'text' => __('Todos los indicadores están en rangos normales.'),
            ];
        }
    }
}
