<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use Livewire\Component;

class ThermalStressCard extends Component
{
    public Plot $plot;
    public ?array $lstData = null;
    public ?array $cwsiData = null;
    public ?array $heatStress = null;
    public ?array $frostRisk = null;
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
                ->whereNotNull('lst_day')
                ->latest('image_date')
                ->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de temperatura disponibles';
                return;
            }

            $this->lstData = [
                'day' => $remoteSensing->lst_day,
                'night' => $remoteSensing->lst_night,
                'diff' => $remoteSensing->lst_diff,
                'date' => $remoteSensing->image_date->format('d/m/Y'),
            ];

            // CWSI data
            if ($remoteSensing->cwsi !== null) {
                $lstService = app(\App\Services\RemoteSensing\NasaLSTService::class);
                $this->cwsiData = $lstService->classifyCWSI($remoteSensing->cwsi);
                $this->cwsiData['value'] = $remoteSensing->cwsi;
            }

            // Check heat stress
            if ($remoteSensing->lst_day) {
                $lstService = app(\App\Services\RemoteSensing\NasaLSTService::class);
                $this->heatStress = $lstService->detectHeatStress(
                    $remoteSensing->lst_day,
                    now()->month
                );
            }

            // Check frost risk
            if ($remoteSensing->lst_night) {
                $lstService = app(\App\Services\RemoteSensing\NasaLSTService::class);
                $this->frostRisk = $lstService->detectFrostRisk(
                    $remoteSensing->lst_night,
                    now()->month
                );
            }

        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos térmicos';
            logger()->error('LST data load failed', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function refresh()
    {
        try {
            $this->loading = true;
            $this->error = null;

            // Trigger enriched data fetch
            $service = app(NasaEarthdataService::class);
            $service->fetchEnrichedData($this->plot, includeArea: false);

            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Datos térmicos actualizados',
            ]);

        } catch (\Exception $e) {
            $this->error = 'Error al actualizar';
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar datos térmicos',
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.thermal-stress-card', [
            'lstData' => $this->lstData,
            'cwsiData' => $this->cwsiData,
            'heatStress' => $this->heatStress,
            'frostRisk' => $this->frostRisk,
            'error' => $this->error,
        ]);
    }
}
