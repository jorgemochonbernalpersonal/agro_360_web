<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Cache;

#[Layout('components.app-layout')]
class ExecutiveDashboard extends Component
{
    public ?int $selectedPlotId = null;
    public ?int $selectedRecintoId = null;
    public $plots = [];
    public $recintos = [];
    public ?Plot $selectedPlot = null;
    public array $summary = [];
    public bool $loading = false;
    public string $generateError = '';

    public function mount()
    {
        $this->loadRecintos();

        if ($this->recintos->isNotEmpty()) {
            $first = $this->recintos->first();
            $this->selectedRecintoId = $first['id'];
            $this->selectedPlotId    = $first['plot_id'];
            $this->loadSummary();
        }
    }

    private function loadRecintos(): void
    {
        // Cargar plots con geometría para el usuario
        $this->plots = Plot::forUser(auth()->user())
            ->whereHas('plotGeometries')
            ->select('id', 'name', 'area', 'viticulturist_id')
            ->orderBy('name')
            ->get();

        // Construir lista plana de recintos: un item por MultipartPlotSigpac con geometría
        $this->recintos = $this->plots->flatMap(function (Plot $plot) {
            return $plot->multiplePlotSigpacs()
                ->whereNotNull('plot_geometry_id')
                ->with('sigpacCode')
                ->get()
                ->map(fn ($mps) => [
                    'id'           => $mps->id,
                    'plot_id'      => $plot->id,
                    'plot_name'    => $plot->name,
                    'sigpac_code'  => $mps->sigpacCode?->formatted_code ?? 'Recinto ' . $mps->id,
                    'display_name' => $plot->name . ' — ' . ($mps->sigpacCode?->formatted_code ?? 'Recinto ' . $mps->id),
                ]);
        });
    }

    public function updatedSelectedRecintoId(): void
    {
        $recinto = $this->recintos->firstWhere('id', $this->selectedRecintoId);
        $this->selectedPlotId = $recinto['plot_id'] ?? null;
        $this->loadSummary();
    }

    public function updatedSelectedPlotId()
    {
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $this->loading = true;

        try {
            $this->selectedPlot = Plot::select('id', 'name', 'area', 'viticulturist_id')
                ->find($this->selectedPlotId);
            
            if (!$this->selectedPlot) {
                $this->summary = [];
                return;
            }

            // Usar caché de 5 minutos para el resumen
            $cacheKey = "executive_dashboard_summary_{$this->selectedPlotId}";
            
            $this->summary = Cache::remember($cacheKey, 300, function () {
                $latestData = $this->selectedPlot->remoteSensingData()
                    ->latest('image_date')
                    ->first();

                if (!$latestData) {
                    return $this->getEmptySummary();
                }

                return [
                    'vigor' => $this->calculateVigorSummary($latestData),
                    'water' => $this->calculateWaterSummary($latestData),
                    'temperature' => $this->calculateTemperatureSummary($latestData),
                    'harvest' => $this->calculateHarvestSummary($latestData),
                    'nutrition' => $this->calculateNutritionSummary($latestData),
                    'alerts' => $this->calculateAlerts($latestData),
                    'last_update' => $latestData->image_date->diffForHumans(),
                    'satellite' => $latestData->satellite ?? 'MODIS',
                ];
            });

        } catch (\Exception $e) {
            logger()->error('Executive dashboard load failed', [
                'plot_id' => $this->selectedPlotId,
                'error' => $e->getMessage(),
            ]);
            $this->summary = $this->getEmptySummary();
        } finally {
            $this->loading = false;
        }
    }

    public function refreshData()
    {
        // Limpiar caché y recargar
        $cacheKey = "executive_dashboard_summary_{$this->selectedPlotId}";
        Cache::forget($cacheKey);
        $this->loadSummary();
    }

    public function generateData()
    {
        if (!$this->selectedPlotId) return;

        $this->generateError = '';

        try {
            $plot = Plot::find($this->selectedPlotId);
            if (!$plot) return;

            $service = app(NasaEarthdataService::class);
            $service->clearCache($plot);
            $service->getLatestData($plot, true);

            Cache::forget("executive_dashboard_summary_{$this->selectedPlotId}");
            $this->loadSummary();
        } catch (\Exception $e) {
            $this->generateError = 'Error al obtener datos: ' . $e->getMessage();
            logger()->error('generateData failed', [
                'plot_id' => $this->selectedPlotId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function calculateVigorSummary(PlotRemoteSensing $data): array
    {
        $ndvi = $data->ndvi_mean ?? 0;
        $gndvi = $data->gndvi ?? 0;
        $lai = $data->lai ?? 0;

        // Determinar estado basado en NDVI
        if ($ndvi >= 0.7) {
            $status = 'excellent';
            $label = 'Excelente';
            $color = 'green';
            $icon = '✅';
        } elseif ($ndvi >= 0.5) {
            $status = 'good';
            $label = 'Bueno';
            $color = 'emerald';
            $icon = '✅';
        } elseif ($ndvi >= 0.3) {
            $status = 'moderate';
            $label = 'Moderado';
            $color = 'yellow';
            $icon = '⚠️';
        } else {
            $status = 'poor';
            $label = 'Bajo';
            $color = 'orange';
            $icon = '⚠️';
        }

        return [
            'ndvi' => $ndvi,
            'gndvi' => $gndvi,
            'lai' => $lai,
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'icon' => $icon,
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

        // Determinar estado basado en CWSI
        if ($cwsi < 0.2) {
            $status = 'excellent';
            $label = 'Sin Estrés';
            $color = 'green';
            $icon = '✅';
        } elseif ($cwsi < 0.4) {
            $status = 'good';
            $label = 'Leve';
            $color = 'yellow';
            $icon = '⚠️';
        } elseif ($cwsi < 0.6) {
            $status = 'moderate';
            $label = 'Moderado';
            $color = 'orange';
            $icon = '⚠️';
        } else {
            $status = 'critical';
            $label = 'Alto Estrés';
            $color = 'red';
            $icon = '🚨';
        }

        return [
            'cwsi' => $cwsi,
            'soil_moisture' => $soilMoisture,
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'icon' => $icon,
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

        // Determinar estado basado en temperatura
        $month = now()->month;
        $threshold = ($month >= 6 && $month <= 8) ? 42 : 38;

        if ($lstDay > $threshold + 5) {
            $status = 'critical';
            $label = 'Estrés Térmico';
            $color = 'red';
            $icon = '🔥';
        } elseif ($lstDay > $threshold) {
            $status = 'warning';
            $label = 'Calor Alto';
            $color = 'orange';
            $icon = '⚠️';
        } elseif ($lstNight < 3 && $month >= 3 && $month <= 5) {
            $status = 'warning';
            $label = 'Riesgo Helada';
            $color = 'blue';
            $icon = '❄️';
        } else {
            $status = 'normal';
            $label = 'Normal';
            $color = 'green';
            $icon = '✅';
        }

        return [
            'lst_day' => $lstDay,
            'lst_night' => $lstNight,
            'lst_diff' => $lstDiff,
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'thermal']),
        ];
    }

    private function calculateHarvestSummary(PlotRemoteSensing $data): array
    {
        $lai = $data->lai ?? null;
        
        if ($lai === null) {
            return $this->getEmptyCard('harvest');
        }

        // Estimación rendimiento desde LAI
        $baseYield = 6.5; // tons/ha for red wine
        $laiFactor = min(1.5, $lai / 2.5);
        $yieldPerHa = $baseYield * $laiFactor;
        $areaHa = $this->selectedPlot->area ?? 1;
        $totalYield = $yieldPerHa * $areaHa;

        // Determinar confianza
        if ($lai >= 1.5 && $lai <= 3.5) {
            $confidence = 'high';
            $confidenceLabel = 'Alta';
            $color = 'green';
        } elseif ($lai >= 1.0 && $lai <= 4.5) {
            $confidence = 'medium';
            $confidenceLabel = 'Media';
            $color = 'yellow';
        } else {
            $confidence = 'low';
            $confidenceLabel = 'Baja';
            $color = 'orange';
        }

        return [
            'lai' => $lai,
            'yield_per_ha' => $yieldPerHa,
            'total_yield' => $totalYield,
            'confidence' => $confidence,
            'confidence_label' => $confidenceLabel,
            'color' => $color,
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

        // Determinar estado nutricional
        if ($gndvi >= 0.6) {
            $status = 'optimal';
            $label = 'Óptimo';
            $color = 'green';
            $icon = '✅';
        } elseif ($gndvi >= 0.5) {
            $status = 'good';
            $label = 'Bueno';
            $color = 'emerald';
            $icon = '✅';
        } elseif ($gndvi >= 0.3) {
            $status = 'low';
            $label = 'Bajo N';
            $color = 'yellow';
            $icon = '⚠️';
        } else {
            $status = 'deficient';
            $label = 'Deficiente';
            $color = 'red';
            $icon = '🚨';
        }

        return [
            'gndvi' => $gndvi,
            'chlorophyll' => $chlorophyll,
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'icon' => $icon,
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'spectral']),
        ];
    }

    private function calculateAlerts(PlotRemoteSensing $data): array
    {
        $alerts = [];
        $critical = 0;
        $warnings = 0;

        // CWSI alto
        if (isset($data->cwsi) && $data->cwsi > 0.6) {
            $alerts[] = ['type' => 'critical', 'message' => 'Estrés hídrico alto'];
            $critical++;
        } elseif (isset($data->cwsi) && $data->cwsi > 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => 'Estrés hídrico moderado'];
            $warnings++;
        }

        // GNDVI bajo
        if (isset($data->gndvi) && $data->gndvi < 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => 'Nivel bajo de nitrógeno'];
            $warnings++;
        }

        // Anomalías detectadas
        if ($data->anomaly_detected) {
            $alerts[] = ['type' => 'critical', 'message' => $data->anomaly_type ?? 'Anomalía detectada'];
            $critical++;
        }

        // LST extremo
        if (isset($data->lst_day) && $data->lst_day > 40) {
            $alerts[] = ['type' => 'critical', 'message' => 'Temperatura superficial muy alta'];
            $critical++;
        }

        return [
            'total' => count($alerts),
            'critical' => $critical,
            'warnings' => $warnings,
            'list' => $alerts,
            'color' => $critical > 0 ? 'red' : ($warnings > 0 ? 'yellow' : 'green'),
            'icon' => $critical > 0 ? '🚨' : ($warnings > 0 ? '⚠️' : '✅'),
            'detail_route' => route('remote-sensing.advanced', ['tab' => 'satellite']),
        ];
    }

    private function getEmptyCard(string $type): array
    {
        $base = [
            'status' => 'no_data',
            'label' => 'Sin Datos',
            'color' => 'gray',
            'icon' => '❓',
            'detail_route' => route('remote-sensing.advanced'),
        ];

        // Añadir campos específicos según el tipo de card
        switch ($type) {
            case 'vigor':
                $base['ndvi'] = 0;
                $base['gndvi'] = 0;
                $base['lai'] = 0;
                break;
            case 'water':
                $base['cwsi'] = null;
                $base['soil_moisture'] = 0;
                break;
            case 'temperature':
                $base['lst_day'] = null;
                $base['lst_night'] = null;
                $base['lst_diff'] = null;
                break;
            case 'harvest':
                $base['lai'] = 0;
                $base['yield_per_ha'] = 0;
                $base['total_yield'] = 0;
                $base['confidence'] = 'low';
                $base['confidence_label'] = 'Sin datos';
                break;
            case 'nutrition':
                $base['gndvi'] = 0;
                $base['chlorophyll'] = null;
                break;
        }

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
            'last_update' => 'Nunca',
            'satellite' => 'N/A',
        ];
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.executive-dashboard');
    }
}
