<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\AdvancedAnalysisService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AdvancedAnalysisCard extends Component
{
    public Plot $plot;

    public ?array $analysis = null;

    public bool $loading = false;

    public ?string $error = null;

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadAnalysis();
    }

    public function loadAnalysis()
    {
        try {
            $this->loading = true;
            $this->error = null;

            $service = app(AdvancedAnalysisService::class);
            $this->analysis = $service->analyzeAdvanced($this->plot);

        } catch (\Exception $e) {
            $this->error = __('Error al cargar el análisis').': '.$e->getMessage();
            logger()->error('Advanced analysis load failed', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function reloadData()
    {
        try {
            $this->loading = true;
            $this->error = null;

            // Clear cache
            $service = app(AdvancedAnalysisService::class);
            $service->clearCache($this->plot);

            // Reload
            $this->analysis = $service->analyzeAdvanced($this->plot, forceRefresh: true);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('Análisis actualizado correctamente'),
            ]);

        } catch (\Exception $e) {
            $this->error = __('Error al actualizar').': '.$e->getMessage();

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Error al actualizar el análisis'),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.advanced-analysis-card');
    }
}
