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
            ->whereNotNull('lst_day')
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
                ->whereNotNull('lst_day');
            
            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }
            
            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de temperatura disponibles para esta fecha';
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

            $recordMonth = $remoteSensing->image_date->month;
            $lstService  = app(\App\Services\RemoteSensing\NasaLSTService::class);

            // Check heat stress using the record's own month, not today's
            if ($remoteSensing->lst_day) {
                $this->heatStress = $lstService->detectHeatStress(
                    $remoteSensing->lst_day,
                    $recordMonth
                );
            }

            // Check frost risk using the record's own month, not today's
            if ($remoteSensing->lst_night) {
                $this->frostRisk = $lstService->detectFrostRisk(
                    $remoteSensing->lst_night,
                    $recordMonth
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

    public function reloadData()
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
            'availableDates' => $this->availableDates,
        ]);
    }
}
