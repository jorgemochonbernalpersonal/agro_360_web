<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use Livewire\Component;

class ThermalStressCard extends Component
{
    public Plot $plot;

    public ?int $sigpacId = null;

    public ?array $lstData = null;

    public ?array $cwsiData = null;

    public ?array $heatStress = null;

    public ?array $frostRisk = null;

    public bool $loading = false;

    public ?string $error = null;

    // Historical date selector
    public ?string $selectedDate = null;

    public array $availableDates = [];

    public function mount(Plot $plot, ?int $sigpacId = null)
    {
        $this->plot = $plot;
        $this->sigpacId = $sigpacId;
        $this->loadAvailableDates();
        $this->loadData();
    }

    public function loadAvailableDates()
    {
        $query = $this->plot->remoteSensingData()
            ->whereNotNull('lst_day');

        if ($this->sigpacId) {
            $query->where('multipart_plot_sigpac_id', $this->sigpacId);
        }

        $dates = $query->orderBy('image_date', 'desc')
            ->limit(30)
            ->pluck('image_date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $this->availableDates = $dates;

        if (empty($this->selectedDate) && ! empty($dates)) {
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

            if ($this->sigpacId) {
                $query->where('multipart_plot_sigpac_id', $this->sigpacId);
            }

            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }

            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (! $remoteSensing) {
                $this->error = __('Sin datos térmicos para este recinto. Pulsa el botón de actualizar.');

                return;
            }

            $this->lstData = [
                'day' => $remoteSensing->lst_day,
                'night' => $remoteSensing->lst_night,
                'diff' => $remoteSensing->lst_diff,
                'date' => $remoteSensing->image_date->format('d/m/Y'),
            ];

            $recordMonth = $remoteSensing->image_date->month;
            $lstService = app(\App\Services\RemoteSensing\NasaLSTService::class);

            if ($remoteSensing->cwsi !== null) {
                $this->cwsiData = $lstService->classifyCWSI($remoteSensing->cwsi);
                $this->cwsiData['value'] = $remoteSensing->cwsi;
            }

            if ($remoteSensing->lst_day) {
                $this->heatStress = $lstService->detectHeatStress($remoteSensing->lst_day, $recordMonth);
            }

            if ($remoteSensing->lst_night) {
                $this->frostRisk = $lstService->detectFrostRisk($remoteSensing->lst_night, $recordMonth);
            }

        } catch (\Exception $e) {
            $this->error = __('Error al cargar datos térmicos');
            logger()->error('LST data load failed', [
                'plot_id' => $this->plot->id,
                'sigpac_id' => $this->sigpacId,
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

            $service = app(\App\Services\RemoteSensing\NasaEarthdataService::class);
            $service->fetchEnrichedData($this->plot, includeArea: false, plotSigpacId: $this->sigpacId);

            $this->loadAvailableDates();
            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('Datos térmicos actualizados'),
            ]);

        } catch (\Exception $e) {
            $this->error = __('Error al actualizar datos térmicos');

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Error al actualizar datos térmicos'),
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
