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

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->loading = true;
            $this->error = null;

            $remoteSensing = $this->plot->remoteSensingData()
                ->whereNotNull('area_statistics')
                ->latest('image_date')
                ->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de área disponibles. Los datos de área se generan bajo demanda.';
                return;
            }

            $this->areaStats = $remoteSensing->area_statistics;
            
            // Create vigor zones
            if ($this->areaStats) {
                $areaService = app(\App\Services\RemoteSensing\NasaAreaRequestService::class);
                $this->vigorZones = $areaService->createVigorZones($this->areaStats);
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

    public function refresh()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.vigor-map-card', [
            'areaStats' => $this->areaStats,
            'vigorZones' => $this->vigorZones,
            'error' => $this->error,
        ]);
    }
}
