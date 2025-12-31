<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\WeatherService;
use Livewire\Component;

/**
 * Weather and soil data card for plot view
 */
class PlotWeatherCard extends Component
{
    public Plot $plot;
    public array $weather = [];
    public array $soil = [];
    public array $solar = [];
    public array $forecast = [];
    public bool $isLoading = false;
    public bool $showForecast = false;
    public string $error = '';

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadData();
    }

    public function loadData(bool $forceRefresh = false)
    {
        $this->isLoading = true;
        $this->error = '';

        $service = new WeatherService();

        // 1. Fetch Weather Data
        try {
            $this->weather = $service->getCurrentWeather($this->plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('PlotWeatherCard: Weather data error', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
            // Fallback for UI handled by view (null checks)
        }

        // 2. Fetch Soil Data
        try {
            $this->soil = $service->getSoilData($this->plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('PlotWeatherCard: Soil data error', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 3. Fetch Solar Data
        try {
            $this->solar = $service->getSolarData($this->plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('PlotWeatherCard: Solar data error', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // General error state only if everything critical failed or to show a toast
        if (empty($this->weather) && empty($this->soil) && empty($this->solar)) {
            $this->error = 'No se pudieron cargar los datos meteorológicos.';
        }

        $this->isLoading = false;
    }

    public function toggleForecast()
    {
        $this->showForecast = !$this->showForecast;
        
        if ($this->showForecast && empty($this->forecast)) {
            $service = new WeatherService();
            $result = $service->getForecast($this->plot, 7);
            $this->forecast = $result['forecast'] ?? [];
        }
    }

    public function refreshData()
    {
        $this->loadData(true);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos meteorológicos actualizados',
        ]);
    }

    /**
     * Get water stress status based on soil moisture and ET0
     */
    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0 = $this->solar['et0'] ?? 3;
        
        // Simple stress calculation
        $stressIndex = ($et0 * 10) - $moisture;
        
        return match (true) {
            $stressIndex <= 0 => ['status' => 'optimal', 'emoji' => '💧', 'text' => 'Óptimo', 'color' => 'text-green-600'],
            $stressIndex <= 20 => ['status' => 'mild', 'emoji' => '💦', 'text' => 'Leve', 'color' => 'text-yellow-600'],
            $stressIndex <= 40 => ['status' => 'moderate', 'emoji' => '🏜️', 'text' => 'Moderado', 'color' => 'text-orange-600'],
            default => ['status' => 'severe', 'emoji' => '⚠️', 'text' => 'Severo', 'color' => 'text-red-600'],
        };
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-weather-card', [
            'waterStress' => $this->getWaterStressStatus(),
        ]);
    }
}
