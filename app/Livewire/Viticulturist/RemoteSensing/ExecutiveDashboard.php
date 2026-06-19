<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Jobs\UpdatePlotSentinel2Job;
use App\Livewire\Viticulturist\RemoteSensing\Traits\HasSummaryCards;
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
    use HasSummaryCards;

    public ?int $selectedPlotId = null;

    public ?int $selectedSigpacId = null;

    public $plots = [];

    public $sigpacs = [];

    public ?Plot $selectedPlot = null;

    public array $summary = [];

    public bool $loading = false;

    public string $generateError = '';

    public array $mapData = [];

    public float $ndviThreshold = 0.30;

    public bool $alertEmailEnabled = false;

    public function mount(): void
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

    public function updatedSelectedPlotId(): void
    {
        $this->loadSummary();
    }

    public function selectPlot(int $plotId): void
    {
        if (! collect($this->plots)->contains('id', $plotId)) {
            return;
        }

        $this->selectedPlotId = $plotId;

        $sigpac = $this->sigpacs->firstWhere('plot_id', $plotId);
        if ($sigpac) {
            $this->selectedSigpacId = $sigpac['id'];
        }

        $this->loadSummary();
    }

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

    public function loadSummary(): void
    {
        $this->loading = true;

        try {
            $this->selectedPlot = Plot::select('id', 'name', 'area', 'viticulturist_id')
                ->find($this->selectedPlotId);

            if (! $this->selectedPlot) {
                $this->summary = [];

                return;
            }

            $pref = PlotAlertPreference::forUser($this->selectedPlotId, auth()->id());
            $this->ndviThreshold = $pref->ndvi_threshold;
            $this->alertEmailEnabled = $pref->email_enabled;

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

    public function refreshData(): void
    {
        Cache::forget("executive_dashboard_summary_{$this->selectedPlotId}");
        $this->loadSummary();
    }

    public function generateData(): void
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

            $sigpacs = MultipartPlotSigpac::where('plot_id', $plot->id)
                ->whereNotNull('plot_geometry_id')
                ->pluck('id');

            if ($sigpacs->isNotEmpty()) {
                foreach ($sigpacs as $sigpacId) {
                    UpdatePlotSentinel2Job::dispatch($plot->id, $sigpacId)->onQueue('remote-sensing');
                }
            } else {
                UpdatePlotSentinel2Job::dispatch($plot->id)->onQueue('remote-sensing');
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
        $this->plots = Plot::forUser(auth()->user())
            ->select('id', 'name', 'area', 'viticulturist_id')
            ->orderBy('name')
            ->get();

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

    private function loadMapData(): void
    {
        if ($this->plots->isEmpty()) {
            return;
        }

        $plotIds = $this->plots->pluck('id')->toArray();

        $geometries = DB::table('multipart_plot_sigpac as mps')
            ->join('plot_geometry as pg', 'pg.id', '=', 'mps.plot_geometry_id')
            ->whereIn('mps.plot_id', $plotIds)
            ->whereNotNull('mps.plot_geometry_id')
            ->selectRaw('mps.plot_id, ST_AsText(pg.coordinates) as wkt')
            ->get()
            ->groupBy('plot_id');

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
}
