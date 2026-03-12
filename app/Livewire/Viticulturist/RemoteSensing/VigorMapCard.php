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

    public function reloadData()
    {
        $this->loadData();
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
