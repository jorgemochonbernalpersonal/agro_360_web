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
                ->whereNotNull('soil_moisture_surface_smap')
                ->latest('image_date')
                ->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos SMAP disponibles';
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

    public function refresh()
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
        ]);
    }
}
