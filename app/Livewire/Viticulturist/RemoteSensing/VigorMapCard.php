<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use Livewire\Component;

class VigorMapCard extends Component
{
    public Plot $plot;
    public ?array $areaStats = null;
    public ?array $vigorZones = null;
    public bool $loading = false;
    public ?string $error = null;
    
    // Historical date selector
    public ?string $selectedDate = null;
    public array $availableDates = [];

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadAvailableDates();
        $this->loadData();
    }

    public function loadAvailableDates()
    {
        $dates = $this->plot->remoteSensingData()
            ->whereNotNull('area_statistics')
            ->orderBy('image_date', 'desc')
            ->limit(30)
            ->pluck('image_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
        
        $this->availableDates = $dates;
        
        if (empty($this->selectedDate) && !empty($dates)) {
            $this->selectedDate = $dates[0];
        }
    }

    public function updatedSelectedDate()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->loading = true;
            $this->error = null;

            $query = $this->plot->remoteSensingData()
                ->whereNotNull('area_statistics');
            
            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }
            
            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de área disponibles para esta fecha. Los datos de área se generan bajo demanda.';
                return;
            }

            $this->areaStats = $remoteSensing->area_statistics;

            // Build vigor zones — prefer real Sentinel-2 zone percentages (improvement #5)
            if ($this->areaStats) {
                $this->vigorZones = $this->buildVigorZones($this->areaStats);
            }

        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos de vigor';
            logger()->error('Vigor map load failed', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function requestAreaData()
    {
        try {
            $this->loading = true;
            $this->error = null;

            $service = app(\App\Services\RemoteSensing\NasaEarthdataService::class);
            $service->fetchEnrichedData($this->plot, includeArea: true);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Solicitud de mapa de vigor enviada. Puede tardar 5-10 minutos en procesarse.',
            ]);

        } catch (\Exception $e) {
            $this->error = 'Error al solicitar datos de área';
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al solicitar mapa de vigor',
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function reloadData(): void
    {
        $this->loadData();
    }

    /**
     * Build human-readable vigor zone array.
     * If Sentinel-2 zone_*_pct values are present (improvement #5), use them as the
     * real pixel-based percentages. Otherwise fall back to the NASA area service.
     */
    private function buildVigorZones(array $areaStats): array
    {
        $hasSentinelZones = isset($areaStats['zone_critical_pct']);

        if (!$hasSentinelZones) {
            // Legacy: delegate to NASA area service
            $areaService = app(\App\Services\RemoteSensing\NasaAreaRequestService::class);
            return $areaService->createVigorZones($areaStats);
        }

        $zones = [
            'excellent' => [
                'label'       => 'Excelente',
                'description' => 'NDVI > 0.65 — vigor muy alto',
                'icon'        => '🌿',
                'color'       => 'green',
                'range'       => ['min' => 0.65, 'max' => 1.0],
                'pct'         => $areaStats['zone_excellent_pct'] ?? 0,
            ],
            'good' => [
                'label'       => 'Bueno',
                'description' => 'NDVI 0.50–0.65 — vigor normal',
                'icon'        => '🍃',
                'color'       => 'lime',
                'range'       => ['min' => 0.50, 'max' => 0.65],
                'pct'         => $areaStats['zone_good_pct'] ?? 0,
            ],
            'moderate' => [
                'label'       => 'Moderado',
                'description' => 'NDVI 0.35–0.50 — estrés leve',
                'icon'        => '🌾',
                'color'       => 'yellow',
                'range'       => ['min' => 0.35, 'max' => 0.50],
                'pct'         => $areaStats['zone_moderate_pct'] ?? 0,
            ],
            'low' => [
                'label'       => 'Bajo',
                'description' => 'NDVI 0.20–0.35 — estrés significativo',
                'icon'        => '🍂',
                'color'       => 'orange',
                'range'       => ['min' => 0.20, 'max' => 0.35],
                'pct'         => $areaStats['zone_low_pct'] ?? 0,
            ],
            'critical' => [
                'label'       => 'Crítico',
                'description' => 'NDVI < 0.20 — suelo desnudo o estrés severo',
                'icon'        => '🚨',
                'color'       => 'red',
                'range'       => ['min' => -1.0, 'max' => 0.20],
                'pct'         => $areaStats['zone_critical_pct'] ?? 0,
            ],
        ];

        return $zones;
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.vigor-map-card', [
            'areaStats' => $this->areaStats,
            'vigorZones' => $this->vigorZones,
            'error' => $this->error,
            'availableDates' => $this->availableDates,
        ]);
    }
}
