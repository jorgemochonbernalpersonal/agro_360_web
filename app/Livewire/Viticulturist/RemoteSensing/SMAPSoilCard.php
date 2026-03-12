<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaSMAPService;
use Livewire\Component;

class SMAPSoilCard extends Component
{
    public Plot $plot;
    public ?array $smapData = null;
    public ?array $comparison = null;
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
            ->whereNotNull('soil_moisture_surface_smap')
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
                ->whereNotNull('soil_moisture_surface_smap');
            
            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }
            
            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos SMAP disponibles para esta fecha';
                return;
            }

            $smapService = app(NasaSMAPService::class);

            // SMAP data
            $surfaceMoisture = $remoteSensing->soil_moisture_surface_smap;
            $rootzoneMoisture = $remoteSensing->soil_moisture_rootzone_smap;

            $this->smapData = [
                'surface' => $surfaceMoisture,
                'rootzone' => $rootzoneMoisture,
                'date' => $remoteSensing->image_date->format('d/m/Y'),
                'surface_status' => $smapService->classifySoilMoisture($surfaceMoisture),
                'rootzone_status' => $smapService->classifySoilMoisture($rootzoneMoisture),
            ];

            // Comparison with Open-Meteo model
            if ($remoteSensing->soil_moisture) {
                $this->comparison = $smapService->compareWithModel(
                    $surfaceMoisture,
                    $remoteSensing->soil_moisture
                );
            }

        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos SMAP';
            logger()->error('SMAP data load failed', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function reloadData()
    {
        $this->loadData();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos SMAP actualizados',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.smap-soil-card', [
            'smapData' => $this->smapData,
            'comparison' => $this->comparison,
            'error' => $this->error,
            'availableDates' => $this->availableDates,
        ]);
    }
}
