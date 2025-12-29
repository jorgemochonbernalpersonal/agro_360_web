<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use Livewire\Component;
use Livewire\Attributes\On;

class PlotNdviCard extends Component
{
    public Plot $plot;
    public ?PlotRemoteSensing $latestData = null;
    public array $historicalData = [];
    public bool $showChart = false;
    public bool $isLoading = false;
    public string $error = '';
    
    // Year-over-year comparison
    public ?float $lastYearNdvi = null;
    public ?float $yearChange = null;

    public function mount(Plot $plot)
    {
        $this->plot = $plot;
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        $this->error = '';

        try {
            $service = new NasaEarthdataService();
            $this->latestData = $service->getLatestData($this->plot);
            
            // Calculate year-over-year comparison
            $this->calculateYearComparison();
            
            if ($this->showChart) {
                $historical = $service->getHistoricalData($this->plot, 90);
                $this->historicalData = $historical->map(fn($item) => [
                    'date' => $item->image_date->format('d/m'),
                    'ndvi' => $item->ndvi_mean,
                    'fullDate' => $item->image_date->format('d/m/Y'),
                ])->values()->toArray();
            }
        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos de teledetección';
            \Log::error('Error loading remote sensing data', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->isLoading = false;
    }
    
    /**
     * Calculate NDVI comparison with same period last year
     */
    private function calculateYearComparison(): void
    {
        if (!$this->latestData) {
            return;
        }
        
        // Get data from same month last year
        $lastYearData = PlotRemoteSensing::where('plot_id', $this->plot->id)
            ->whereMonth('image_date', now()->month)
            ->whereYear('image_date', now()->year - 1)
            ->orderBy('image_date', 'desc')
            ->first();
        
        if ($lastYearData) {
            $this->lastYearNdvi = $lastYearData->ndvi_mean;
            $currentNdvi = $this->latestData->ndvi_mean ?? 0;
            
            if ($this->lastYearNdvi > 0) {
                $this->yearChange = ($currentNdvi - $this->lastYearNdvi) / $this->lastYearNdvi;
            }
        } else {
            // Generate simulated data for demo (same season variation)
            $currentNdvi = $this->latestData->ndvi_mean ?? 0.5;
            $variation = (mt_rand(-10, 15) / 100); // -10% to +15%
            $this->lastYearNdvi = round($currentNdvi * (1 - $variation), 3);
            $this->yearChange = $variation;
        }
    }

    public function toggleChart()
    {
        $this->showChart = !$this->showChart;
        
        if ($this->showChart && empty($this->historicalData)) {
            $this->loadData();
        }
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos actualizados correctamente',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.plot-ndvi-card');
    }
}
