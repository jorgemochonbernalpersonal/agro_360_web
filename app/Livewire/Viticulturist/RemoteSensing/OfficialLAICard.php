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
            ->whereNotNull('lai')
            ->orderBy('image_date', 'desc')
            ->limit(30)
            ->pluck('image_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
        
        $this->availableDates = $dates;
        
        if (empty($this->selectedDate) && !empty($dates)) {
            $this->selectedDate = $dates[0]; // Most recent
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
                ->whereNotNull('lai');
            
            // Filter by selected date or get latest
            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }
            
            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de LAI disponibles para esta fecha';
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

    public function reloadLAI()
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
            'availableDates' => $this->availableDates,
        ]);
    }
}
