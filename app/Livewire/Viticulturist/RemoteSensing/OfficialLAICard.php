<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaLAIService;
use Livewire\Component;

class OfficialLAICard extends Component
{
    public Plot $plot;
    public ?array $laiData = null;
    public ?array $fparData = null;
    public ?array $yieldEstimate = null;
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
                ->whereNotNull('lai')
                ->latest('image_date')
                ->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de LAI disponibles';
                return;
            }

            $laiService = app(NasaLAIService::class);

            // LAI data
            if ($remoteSensing->lai) {
                $classification = $laiService->classifyLAI($remoteSensing->lai);
                
                $this->laiData = array_merge([
                    'value' => $remoteSensing->lai,
                    'date' => $remoteSensing->image_date->format('d/m/Y'),
                    'source' => 'NASA MODIS (Oficial)',
                ], $classification);

                // Yield estimate
                $areaHa = $this->plot->area ?? 1;
                $varietyType = 'red'; // TODO: Get from plot data
                $this->yieldEstimate = $laiService->estimateYield($remoteSensing->lai, $areaHa, $varietyType);
            }

            // FPAR data
            if ($remoteSensing->fpar) {
                $this->fparData = $laiService->analyzeFPAR($remoteSensing->fpar);
            }

        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos de LAI';
            logger()->error('LAI data load failed', [
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
            'message' => 'Datos de LAI actualizados',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.official-lai-card', [
            'laiData' => $this->laiData,
            'fparData' => $this->fparData,
            'yieldEstimate' => $this->yieldEstimate,
            'error' => $this->error,
        ]);
    }
}
