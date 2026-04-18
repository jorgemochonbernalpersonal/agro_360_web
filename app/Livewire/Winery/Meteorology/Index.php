<?php

namespace App\Livewire\Winery\Meteorology;

use App\Models\Plot;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $selectedPlot = '';
    public array $weather = [];
    public array $soil = [];
    public array $solar = [];
    public array $forecast = [];
    public bool $showForecast = false;
    public string $error = '';

    public function mount(): void
    {
        $firstPlot = Plot::where('viticulturist_id', Auth::id())
            ->orderBy('name')
            ->first();

        if ($firstPlot) {
            $this->selectedPlot = (string) $firstPlot->id;
            $this->loadWeatherData();
        }
    }

    public function updatedSelectedPlot(): void
    {
        $this->reset(['weather', 'soil', 'solar', 'forecast', 'showForecast', 'error']);
        if ($this->selectedPlot) {
            $this->loadWeatherData();
        }
    }

    public function loadWeatherData(bool $forceRefresh = false): void
    {
        $this->error = '';
        $plot = Plot::where('viticulturist_id', Auth::id())->find($this->selectedPlot);

        if (! $plot) {
            $this->error = 'Parcela no encontrada.';
            return;
        }

        $service = new WeatherService();

        try {
            $this->weather = $service->getCurrentWeather($plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('Winery Meteorology: Weather error', ['plot' => $plot->id, 'error' => $e->getMessage()]);
        }

        try {
            $this->soil = $service->getSoilData($plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('Winery Meteorology: Soil error', ['plot' => $plot->id, 'error' => $e->getMessage()]);
        }

        try {
            $this->solar = $service->getSolarData($plot, $forceRefresh);
        } catch (\Exception $e) {
            \Log::error('Winery Meteorology: Solar error', ['plot' => $plot->id, 'error' => $e->getMessage()]);
        }

        if (empty($this->weather) && empty($this->soil) && empty($this->solar)) {
            $this->error = 'No se pudieron cargar los datos meteorologicos para esta parcela.';
        }
    }

    public function toggleForecast(): void
    {
        $this->showForecast = ! $this->showForecast;

        if ($this->showForecast && empty($this->forecast)) {
            $plot = Plot::where('viticulturist_id', Auth::id())->find($this->selectedPlot);
            if ($plot) {
                $service = new WeatherService();
                $result = $service->getForecast($plot, 7);
                $this->forecast = $result['forecast'] ?? [];
            }
        }
    }

    public function refreshData(): void
    {
        $this->loadWeatherData(true);
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Datos meteorologicos actualizados',
        ]);
    }

    public function getWaterStressStatus(): array
    {
        $moisture = $this->soil['soil_moisture'] ?? 50;
        $et0      = $this->solar['et0'] ?? 3;

        $stressIndex = ($et0 * 10) - $moisture;

        return match (true) {
            $stressIndex <= 0  => ['status' => 'optimal',  'text' => 'Optimo',   'color' => 'green',  'icon' => 'check-circle'],
            $stressIndex <= 20 => ['status' => 'mild',     'text' => 'Leve',     'color' => 'amber',  'icon' => 'minus-circle'],
            $stressIndex <= 40 => ['status' => 'moderate', 'text' => 'Moderado', 'color' => 'orange', 'icon' => 'exclamation-triangle'],
            default            => ['status' => 'severe',   'text' => 'Severo',   'color' => 'red',    'icon' => 'exclamation-circle'],
        };
    }

    public function render()
    {
        $plots = Plot::where('viticulturist_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'municipality', 'surface_area']);

        $plotSummaries = [];
        if ($plots->count() <= 12) {
            $service = new WeatherService();
            foreach ($plots as $plot) {
                try {
                    $w = $service->getCurrentWeather($plot);
                    $plotSummaries[$plot->id] = [
                        'name'          => $plot->name,
                        'temperature'   => $w['temperature'] ?? null,
                        'weather_code'  => $w['weather_code'] ?? 0,
                        'humidity'      => $w['humidity'] ?? null,
                        'precipitation' => $w['precipitation'] ?? 0,
                    ];
                } catch (\Exception $e) {
                    $plotSummaries[$plot->id] = ['name' => $plot->name, 'temperature' => null];
                }
            }
        }

        return view('livewire.winery.meteorology.index', [
            'plots'         => $plots,
            'plotSummaries' => $plotSummaries,
            'waterStress'   => $this->getWaterStressStatus(),
        ])->layout('layouts.app', [
            'title'       => 'Meteorologia',
            'description' => 'Datos meteorologicos en tiempo real de tus parcelas',
        ]);
    }
}
