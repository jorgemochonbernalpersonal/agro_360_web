<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Jobs\UpdatePlotSentinel2Job;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\PlotAlertPreference;
use App\Models\PlotRemoteSensing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.app-layout')]
class ExecutiveDashboard extends Component
{
    public ?int $selectedPlotId = null;

    public ?int $selectedSigpacId = null;

    public $plots = [];

    public $sigpacs = [];

    public ?Plot $selectedPlot = null;

    public array $summary = [];

    public bool $loading = false;

    public string $generateError = '';

    // Vigor map data (all plots with geometries + NDVI color)
    public array $mapData = [];

    // Per-plot alert settings
    public float $ndviThreshold = 0.30;

    public bool $alertEmailEnabled = false;

    public function mount()
    {
        $this->loadSigpacs();

        if ($this->sigpacs->isNotEmpty()) {
            $first = $this->sigpacs->first();
            $this->selectedSigpacId = $first['id'];
            $this->selectedPlotId = $first['plot_id'];
            $this->loadSummary();
        }
    }

    public function updatedSelectedSigpacId(): void
    {
        $sigpac = $this->sigpacs->firstWhere('id', $this->selectedSigpacId);
        $this->selectedPlotId = $sigpac['plot_id'] ?? null;
        $this->loadSummary();
    }

    public function updatedSelectedPlotId()
    {
        $this->loadSummary();
    }

    /**
     * Called from the Leaflet map when the user clicks a plot polygon.
     */
    public function selectPlot(int $plotId): void
    {
        if (! collect($this->plots)->contains('id', $plotId)) {
            return; // unauthorized or unknown plot
        }

        $this->selectedPlotId = $plotId;

        // Sync the sigpac selector to the first sigpac parcel of this plot
        $sigpac = $this->sigpacs->firstWhere('plot_id', $plotId);
        if ($sigpac) {
            $this->selectedSigpacId = $sigpac['id'];
        }

        $this->loadSummary();
    }

    /**
     * Save NDVI alert threshold and email toggle for the authenticated user + selected plot.
     * Each user (viticulturist, winery, supervisor) keeps their own independent preferences.
     */
    public function saveAlertSettings(): void
    {
        $this->validate([
            'ndviThreshold' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        if (! $this->selectedPlotId) {
            return;
        }

        PlotAlertPreference::updateOrCreate(
            ['plot_id' => $this->selectedPlotId, 'user_id' => auth()->id()],
            ['ndvi_threshold' => $this->ndviThreshold, 'email_enabled' => $this->alertEmailEnabled]
        );

        $this->dispatch('notify', message: __('Configuración de alertas guardada.'));
    }

    public function loadSummary()
    {
        $this->loading = true;

        try {
            $this->selectedPlot = Plot::select('id', 'name', 'area', 'viticulturist_id')
                ->find($this->selectedPlotId);

            if (! $this->selectedPlot) {
                $this->summary = [];

                return;
            }

            // Load this user's own alert preferences for the selected plot
            $pref = PlotAlertPreference::forUser($this->selectedPlotId, auth()->id());
            $this->ndviThreshold = $pref->ndvi_threshold;
            $this->alertEmailEnabled = $pref->email_enabled;

            // Usar caché de 5 minutos para el resumen
            $cacheKey = "executive_dashboard_summary_{$this->selectedPlotId}";

            $this->summary = Cache::remember($cacheKey, 300, function () {
                $latestData = $this->selectedPlot->remoteSensingData()
                    ->latest('image_date')
                    ->first();

                if (! $latestData) {
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
                    'satellite' => $latestData->image_source ?? 'MODIS',
                    'is_estimated' => str_contains($latestData->image_source ?? '', 'Estimado') || str_contains($latestData->image_source ?? '', 'Mock'),
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
        if (! $this->selectedPlotId) {
            return;
        }

        $this->generateError = '';

        try {
            $plot = Plot::find($this->selectedPlotId);
            if (! $plot) {
                return;
            }

            // Dispatch Sentinel-2 jobs for each sigpac parcel with geometry.
            // Falls back to a single plot-level job if no sigpac geometries exist.
            $sigpacs = MultipartPlotSigpac::where('plot_id', $plot->id)
                ->whereNotNull('plot_geometry_id')
                ->pluck('id');

            if ($sigpacs->isNotEmpty()) {
                foreach ($sigpacs as $sigpacId) {
                    UpdatePlotSentinel2Job::dispatch($plot->id, $sigpacId)
                        ->onQueue('remote-sensing');
                }
            } else {
                UpdatePlotSentinel2Job::dispatch($plot->id)
                    ->onQueue('remote-sensing');
            }

            Cache::forget("executive_dashboard_summary_{$this->selectedPlotId}");

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('Actualización Sentinel-2 en proceso. Los datos estarán disponibles en unos momentos.'),
            ]);

        } catch (\Exception $e) {
            $this->generateError = 'Error al encolar la actualización: '.$e->getMessage();
            logger()->error('generateData (Sentinel-2 dispatch) failed', [
                'plot_id' => $this->selectedPlotId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.executive-dashboard');
    }

    private function loadSigpacs(): void
    {
        // Cargar todas las parcelas del usuario (para saber si tiene alguna)
        $this->plots = Plot::forUser(auth()->user())
            ->select('id', 'name', 'area', 'viticulturist_id')
            ->orderBy('name')
            ->get();

        // Build flat list of sigpac parcels: one item per MultipartPlotSigpac with geometry
        $this->sigpacs = $this->plots->flatMap(function (Plot $plot) {
            return $plot->multiplePlotSigpacs()
                ->whereNotNull('plot_geometry_id')
                ->with('sigpacCode')
                ->get()
                ->map(fn ($mps) => [
                    'id' => $mps->id,
                    'plot_id' => $plot->id,
                    'plot_name' => $plot->name,
                    'sigpac_code' => $mps->sigpacCode->formatted_code ?? 'Recinto '.$mps->id,
                    'display_name' => $plot->name.' — '.($mps->sigpacCode->formatted_code ?? 'Recinto '.$mps->id),
                ]);
        });

        $this->loadMapData();
    }

    /**
     * Build map data: WKT geometries + NDVI colors for all user plots.
     * Uses a single spatial query to avoid N+1.
     */
    private function loadMapData(): void
    {
        if ($this->plots->isEmpty()) {
            return;
        }

        $plotIds = $this->plots->pluck('id')->toArray();

        // One query: all WKT geometries for user's plots
        $geometries = DB::table('multipart_plot_sigpac as mps')
            ->join('plot_geometry as pg', 'pg.id', '=', 'mps.plot_geometry_id')
            ->whereIn('mps.plot_id', $plotIds)
            ->whereNotNull('mps.plot_geometry_id')
            ->selectRaw('mps.plot_id, ST_AsText(pg.coordinates) as wkt')
            ->get()
            ->groupBy('plot_id');

        // Latest NDVI per plot
        $latestNdvi = PlotRemoteSensing::whereIn('plot_id', $plotIds)
            ->whereIn('id', function ($q) use ($plotIds) {
                $q->selectRaw('MAX(id)')
                    ->from('plot_remote_sensing')
                    ->whereIn('plot_id', $plotIds)
                    ->groupBy('plot_id');
            })
            ->get()
            ->keyBy('plot_id');

        $this->mapData = $this->plots
            ->map(function (Plot $plot) use ($geometries, $latestNdvi) {
                $plotGeoms = $geometries->get($plot->id, collect());

                if ($plotGeoms->isEmpty()) {
                    return null;
                }

                $latest = $latestNdvi->get($plot->id);
                $ndvi = $latest?->ndvi_mean !== null ? (float) $latest->ndvi_mean : null;
                $color = $this->getNdviColor($ndvi);

                return [
                    'plot_id' => $plot->id,
                    'plot_name' => $plot->name,
                    'ndvi' => $ndvi !== null ? round($ndvi, 3) : null,
                    'health_status' => $latest->health_status ?? 'no_data',
                    'fill' => $color['fill'],
                    'line' => $color['line'],
                    'wkts' => $plotGeoms->pluck('wkt')->filter()->values()->toArray(),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
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

    private function calculateVigorSummary(PlotRemoteSensing $data): array
    {
        $ndvi = $data->ndvi_mean ?? 0;
        $gndvi = $data->gndvi ?? 0;
        $lai = $data->lai ?? 0;

        // Determinar estado basado en NDVI
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
            $alerts[] = ['type' => 'critical', 'message' => __('Estrés hídrico alto')];
            $critical++;
        } elseif (isset($data->cwsi) && $data->cwsi > 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => __('Estrés hídrico moderado')];
            $warnings++;
        }

        // GNDVI bajo
        if (isset($data->gndvi) && $data->gndvi < 0.4) {
            $alerts[] = ['type' => 'warning', 'message' => __('Nivel bajo de nitrógeno')];
            $warnings++;
        }

        // Anomalías detectadas
        if ($data->anomaly_detected) {
            $alerts[] = ['type' => 'critical', 'message' => $data->anomaly_type ?? 'Anomalía detectada'];
            $critical++;
        }

        // LST extremo
        if (isset($data->lst_day) && $data->lst_day > 40) {
            $alerts[] = ['type' => 'critical', 'message' => __('Temperatura superficial muy alta')];
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
            'label' => __('Sin Datos'),
            'color' => 'gray',
            'icon' => '❓',
            'detail_route' => route('remote-sensing.advanced'),
        ];

        // Añadir campos específicos según el tipo de card
        switch ($type) {
            case 'vigor':
                $base['ndvi'] = 0;
                $base['gndvi'] = null;
                $base['lai'] = null;
                break;
            case 'water':
                $base['cwsi'] = null;
                $base['soil_moisture'] = null;
                break;
            case 'temperature':
                $base['lst_day'] = null;
                $base['lst_night'] = null;
                $base['lst_diff'] = null;
                break;
            case 'harvest':
                $base['lai'] = null;
                $base['yield_per_ha'] = null;
                $base['total_yield'] = null;
                $base['confidence'] = 'low';
                $base['confidence_label'] = 'Sin datos';
                break;
            case 'nutrition':
                $base['gndvi'] = null;
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
            'last_update' => __('Nunca'),
            'satellite' => __('N/A'),
            'is_estimated' => false,
        ];
    }
}
