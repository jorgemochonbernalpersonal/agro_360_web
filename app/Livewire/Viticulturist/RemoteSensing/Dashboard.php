<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Livewire\Viticulturist\RemoteSensing\Traits\HasAgriculturalCalculations;
use App\Livewire\Viticulturist\RemoteSensing\Traits\HasHistoricalAnalysis;
use App\Livewire\Viticulturist\RemoteSensing\Traits\HasRecommendations;
use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\DataSourceHealthService;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Unified Remote Sensing Dashboard with plot selector and analysis tabs.
 * Business logic is split across three traits to keep this file manageable:
 *   - HasHistoricalAnalysis  (history chart, period comparison, trend prediction, NDVI baseline)
 *   - HasAgriculturalCalculations (irrigation, GDD, water stress, year comparison)
 *   - HasRecommendations     (recommendation engine, frost alerts)
 */
#[Layout('components.app-layout')]
class Dashboard extends Component
{
    use HasHistoricalAnalysis, HasAgriculturalCalculations, HasRecommendations;

    #[Url(as: 'tab')]
    public string $activeTab = 'satellite';

    #[Url(as: 'sigpac')]
    public ?int $selectedSigpacId = null;

    // All sigpac recintos (all plots) for selector
    public array $allSigpacs = [];
    // Plots kept for stats and compare tab
    public $plots = [];
    public array $stats = [];

    // Selected plot data
    public ?Plot $selectedPlot = null;
    public ?PlotRemoteSensing $ndviData = null;
    public array $historicalData = [];
    public ?float $lastYearNdvi = null;
    public ?float $yearChange = null;

    // Date selector for satellite tab
    public ?string $satelliteSelectedDate = null;
    public array $satelliteAvailableDates = [];
    public bool $loadingDataForDate = false;
    public ?string $dataLoadError = null;

    // Historical data filters
    public string $historyPeriod = '90_days';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public int $historyDays = 90;

    // Period comparison
    public bool $showComparison = false;
    public string $comparisonPeriod = 'last_year';
    public array $comparisonData = [];

    // Alerts and predictions
    public float $ndviThreshold = 0.3;
    public array $periodAlerts = [];
    public array $trendPrediction = [];

    // Historical NDVI baseline for chart (improvement #8)
    public array $ndviBaseline = [];

    // Data source health status (improvement #6)
    public array $dataSourceStatus = [];

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

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $this->plots = Plot::forUser($user)
            ->whereHas('plotGeometries')
            ->select(['id', 'name', 'area'])
            ->orderBy('name')
            ->get();

        $this->loadStats();
        $this->loadAllSigpacs();

        // Validate URL-injected selectedSigpacId belongs to user's authorized sigpacs
        if ($this->selectedSigpacId && !collect($this->allSigpacs)->contains('id', $this->selectedSigpacId)) {
            $this->selectedSigpacId = null;
        }

        // Auto-select first sigpac if none set
        if (!$this->selectedSigpacId && !empty($this->allSigpacs)) {
            $this->selectedSigpacId = $this->allSigpacs[0]['id'];
        }

        if ($this->selectedSigpacId) {
            $this->deriveSelectedPlot();
            $this->loadPlotData();
        }

        // Improvement #6: data source health badges
        $this->dataSourceStatus = app(DataSourceHealthService::class)->getStatus();
    }

    public function updatedSelectedSigpacId(): void
    {
        // Prevent IDOR
        if (!collect($this->allSigpacs)->contains('id', $this->selectedSigpacId)) {
            abort(403);
        }

        $this->deriveSelectedPlot();
        $this->loadPlotData(forceRefresh: true);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = match ($tab) {
            'spectral', 'lai-official', 'vigor-map' => 'satellite',
            'smap-soil'                             => 'soil',
            'solar'                                 => 'weather',
            default                                 => $tab,
        };

        if ($this->activeTab === 'compare' && $this->comparePlotId) {
            $this->loadComparisonData();
        }
    }

    public function updatedComparePlotId(): void
    {
        if ($this->comparePlotId && $this->activeTab === 'compare') {
            $this->loadComparisonData();
        }
    }

    public function loadComparisonData(): void
    {
        if (!$this->comparePlotId) return;

        $this->comparePlot = Plot::find($this->comparePlotId);
        if (!$this->comparePlot) return;

        try {
            $nasaService          = app(NasaEarthdataService::class);
            $this->compareNdviData = $nasaService->getLatestData($this->comparePlot);

            $weatherService       = new WeatherService();
            $this->compareWeather = $weatherService->getCurrentWeather($this->comparePlot);
            $this->compareSoil    = $weatherService->getSoilData($this->comparePlot);
            $this->compareSolar   = $weatherService->getSolarData($this->comparePlot);
        } catch (\Exception $e) {
            Log::error('Comparison data error', ['error' => $e->getMessage()]);
        }
    }

    public function loadStats(): void
    {
        $plots   = collect($this->plots);
        $plotIds = $plots->pluck('id');

        if ($plotIds->isEmpty()) {
            $this->stats = [
                'total_plots' => 0, 'with_data' => 0, 'average_ndvi' => 0,
                'excellent' => 0, 'good' => 0, 'moderate' => 0, 'poor' => 0, 'critical' => 0,
            ];
            return;
        }

        $latestNdviData = \App\Models\PlotRemoteSensing::whereIn('plot_id', $plotIds)
            ->whereIn('id', function ($subQuery) use ($plotIds) {
                $subQuery->selectRaw('MAX(id)')->from('plot_remote_sensing')
                    ->whereIn('plot_id', $plotIds)->groupBy('plot_id');
            })
            ->get()
            ->keyBy('plot_id');

        $excellent = $good = $moderate = $poor = $critical = 0;
        $totalNdvi = 0;
        $ndviCount = 0;

        foreach ($plots as $plot) {
            $data = $latestNdviData->get($plot->id);
            if ($data) {
                $ndviCount++;
                $totalNdvi += $data->ndvi_mean ?? 0;
                match ($data->health_status) {
                    'excellent' => $excellent++,
                    'good'      => $good++,
                    'moderate'  => $moderate++,
                    'poor'      => $poor++,
                    'critical'  => $critical++,
                    default     => null,
                };
            }
        }

        $this->stats = [
            'total_plots'  => $plots->count(),
            'with_data'    => $ndviCount,
            'average_ndvi' => $ndviCount > 0 ? round($totalNdvi / $ndviCount, 3) : 0,
            'excellent'    => $excellent,
            'good'         => $good,
            'moderate'     => $moderate,
            'poor'         => $poor,
            'critical'     => $critical,
            'alerts'       => $poor + $critical,
        ];
    }

    public function loadPlotData(bool $forceRefresh = false): void
    {
        if (!$this->selectedSigpacId) return;

        $this->isLoading = true;

        if (!$this->selectedPlot) {
            $this->isLoading = false;
            return;
        }

        try {
            $this->loadSatelliteAvailableDates();
            $this->loadSatelliteData($forceRefresh);
            $this->loadHistoricalData();
            $this->calculateYearComparison();

            $weatherService  = new WeatherService();
            $this->weather   = $weatherService->getCurrentWeather($this->selectedPlot, $forceRefresh);
            $this->soil      = $weatherService->getSoilData($this->selectedPlot, $forceRefresh);
            $this->solar     = $weatherService->getSolarData($this->selectedPlot, $forceRefresh);
            $this->forecast  = $weatherService->getForecast($this->selectedPlot, 7, $forceRefresh)['forecast'] ?? [];

            $this->generateRecommendations();

            // Refresh data source health after a fetch cycle
            $this->dataSourceStatus = app(DataSourceHealthService::class)->getStatus();
        } catch (\Exception $e) {
            Log::error('Dashboard load error', ['error' => $e->getMessage()]);
        }

        $this->isLoading = false;
    }

    private function loadSatelliteAvailableDates(): void
    {
        if (!$this->selectedPlot) return;

        $query = $this->selectedPlot->remoteSensingData()->whereNotNull('ndvi_mean');

        if ($this->selectedSigpacId) {
            $query->where('multipart_plot_sigpac_id', $this->selectedSigpacId);
        }

        $dates = $query->orderBy('image_date', 'desc')
            ->limit(30)
            ->pluck('image_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();

        $this->satelliteAvailableDates = $dates;
        $this->satelliteSelectedDate   = $dates[0] ?? null;
    }

    private function loadSatelliteData(bool $forceRefresh = false): void
    {
        if (!$this->selectedPlot) return;

        $query = $this->selectedPlot->remoteSensingData()->whereNotNull('ndvi_mean');

        if ($this->selectedSigpacId) {
            $query->where('multipart_plot_sigpac_id', $this->selectedSigpacId);
        }

        if ($this->satelliteSelectedDate) {
            $this->ndviData = $query->whereDate('image_date', $this->satelliteSelectedDate)
                ->orderBy('image_date', 'desc')
                ->first();
        } else {
            $this->ndviData = $query->orderBy('image_date', 'desc')->first();
        }
    }

    public function updatedSatelliteSelectedDate(): void
    {
        $this->loadSatelliteData();
        $this->calculateYearComparison();
        $this->generateRecommendations();
    }

    public function requestDataForDate(?string $date = null): void
    {
        if (!$this->selectedPlot) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('No hay parcela seleccionada')]);
            return;
        }

        try {
            $service = app(\App\Services\RemoteSensing\CopernicusSentinel2Service::class);
            $result  = $service->fetchAndStore($this->selectedPlot, $this->selectedSigpacId);

            if ($result) {
                $this->loadSatelliteData();
                $this->loadSatelliteAvailableDates();
                $this->dispatch('notify', ['type' => 'success', 'message' => __('Datos Sentinel-2 actualizados correctamente.')]);
            } else {
                $this->dispatch('notify', ['type' => 'warning', 'message' => __('No se pudieron obtener datos Sentinel-2 para esta parcela.')]);
            }
        } catch (\Exception $e) {
            Log::error('Sentinel-2 manual fetch failed', ['plot_id' => $this->selectedPlot->id, 'error' => $e->getMessage()]);
            $this->dispatch('notify', ['type' => 'error', 'message' => __('Error al obtener datos: :message', ['message' => $e->getMessage()])]);
        }
    }

    public function requestInitialData(): void
    {
        if (!$this->selectedPlot) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('No hay parcela seleccionada')]);
            return;
        }

        $this->loadingDataForDate = true;

        try {
            \App\Jobs\UpdatePlotNdviJob::dispatch($this->selectedPlot)->onQueue('default');
            $this->dispatch('notify', ['type' => 'info', 'message' => __('Solicitando datos satelitales... Se actualizará en 1-2 minutos.')]);
            $this->dispatch('poll-for-data', ['plotId' => $this->selectedPlot->id, 'delay' => 30000]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('Error al solicitar datos: :message', ['message' => $e->getMessage()])]);
        } finally {
            $this->loadingDataForDate = false;
        }
    }

    private function loadAllSigpacs(): void
    {
        $plotIds = collect($this->plots)->pluck('id');

        if ($plotIds->isEmpty()) {
            $this->allSigpacs = [];
            return;
        }

        $this->allSigpacs = \App\Models\MultipartPlotSigpac::whereIn('plot_id', $plotIds)
            ->whereNotNull('plot_geometry_id')
            ->with(['sigpacCode', 'plotGeometry', 'plot'])
            ->get()
            ->map(function ($mps) {
                $geometry = $mps->plotGeometry;
                $centroid = $geometry?->getCentroidAsArray();
                $area     = null;
                if ($geometry) {
                    $points = $geometry->getCoordinatesAsArray();
                    $area   = $this->polygonAreaHa($points);
                }

                return [
                    'id'           => $mps->id,
                    'plot_id'      => $mps->plot_id,
                    'plot_name'    => $mps->plot->name ?? 'Sin parcela',
                    'sigpac_code'  => $mps->sigpacCode?->code ?? 'Sin código',
                    'display_name' => ($mps->plot->name ?? 'Parcela') . ' · ' . ($mps->sigpacCode?->code ?? 'Recinto ' . $mps->id),
                    'area_ha'      => $area ? round($area, 2) : 0,
                    'centroid'     => $centroid,
                ];
            })
            ->toArray();
    }

    private function deriveSelectedPlot(): void
    {
        if (!$this->selectedSigpacId) {
            $this->selectedPlot = null;
            return;
        }

        $sigpac             = collect($this->allSigpacs)->firstWhere('id', $this->selectedSigpacId);
        $this->selectedPlot = $sigpac ? Plot::find($sigpac['plot_id']) : null;
    }

    public function getSelectedSigpacCoordinates(): ?array
    {
        if (!$this->selectedSigpacId || empty($this->allSigpacs)) {
            return null;
        }

        $sigpac = collect($this->allSigpacs)->firstWhere('id', $this->selectedSigpacId);
        return $sigpac['centroid'] ?? null;
    }

    public function refreshData(): void
    {
        $nasaService = app(NasaEarthdataService::class);
        $nasaService->clearCache($this->selectedPlot);

        $plotId = $this->selectedPlot?->id;
        Cache::forget("weather_{$plotId}");
        Cache::forget("forecast_{$plotId}_7");
        Cache::forget("soil_{$plotId}");
        Cache::forget("solar_{$plotId}");

        $this->loadPlotData(true);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('Datos actualizados correctamente')]);
    }

    public function downloadReport(): void
    {
        if (!$this->selectedPlot) return;

        try {
            $service = new \App\Services\RemoteSensing\RemoteSensingReportService();
            $result  = $service->generatePlotReport($this->selectedPlot);

            if ($result['success']) {
                $this->dispatch('notify', ['type' => 'success', 'message' => __('Informe generado correctamente. Descargando...')]);
                // Note: download response must be returned directly in Livewire — handled by caller
                $service->downloadReport($result['pdf_path']);
            }
        } catch (\Exception $e) {
            Log::error('Report generation error', ['error' => $e->getMessage()]);
            $this->dispatch('notify', ['type' => 'error', 'message' => __('Error al generar el informe: :message', ['message' => $e->getMessage()])]);
        }
    }

    /**
     * Shoelace formula with per-latitude metre correction.
     * Returns area in hectares from an array of ['lat'=>..., 'lng'=>...] points.
     */
    private function polygonAreaHa(array $points): float
    {
        $n = count($points);
        if ($n < 3) return 0.0;

        $latSum = array_sum(array_column($points, 'lat'));
        $latRad = deg2rad($latSum / $n);
        $mLat   = 111320.0;
        $mLng   = 111320.0 * cos($latRad);
        $area   = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j     = ($i + 1) % $n;
            $xi    = $points[$i]['lng'] * $mLng;
            $yi    = $points[$i]['lat'] * $mLat;
            $xj    = $points[$j]['lng'] * $mLng;
            $yj    = $points[$j]['lat'] * $mLat;
            $area += $xi * $yj - $xj * $yi;
        }

        return round(abs($area) / 2 / 10000, 2);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.dashboard', [
            'waterStress'    => $this->getWaterStressStatus(),
            'irrigationNeeds'=> $this->getIrrigationNeeds(),
            'gdd'            => $this->getGrowingDegreeDays(),
        ]);
    }
}
