<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaSMAPService;
use Livewire\Component;

class SmapSoilCard extends Component
{
    public Plot $plot;
    public ?int $sigpacId = null;
    public ?array $smapData = null;
    public bool $loading = false;
    public ?string $error = null;

    // Historical date selector
    public ?string $selectedDate = null;
    public array $availableDates = [];

    public function mount(Plot $plot, ?int $sigpacId = null)
    {
        $this->plot     = $plot;
        $this->sigpacId = $sigpacId;
        $this->loadAvailableDates();
        $this->loadData();
    }

    public function loadAvailableDates()
    {
        $query = $this->plot->remoteSensingData()
            ->whereNotNull('soil_moisture_surface_smap');

        if ($this->sigpacId) {
            $query->where('multipart_plot_sigpac_id', $this->sigpacId);
        }

        $dates = $query->orderBy('image_date', 'desc')
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
            $this->error   = null;

            $query = $this->plot->remoteSensingData()
                ->whereNotNull('soil_moisture_surface_smap');

            if ($this->sigpacId) {
                $query->where('multipart_plot_sigpac_id', $this->sigpacId);
            }

            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }

            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = __('Sin datos de humedad para este recinto. Pulsa el botón de actualizar.');
                return;
            }

            $smapService     = app(NasaSMAPService::class);
            $surfaceMoisture = $remoteSensing->soil_moisture_surface_smap;
            $rootzoneMoisture = $remoteSensing->soil_moisture_rootzone_smap;

            $this->smapData = [
                'surface'        => $surfaceMoisture,
                'rootzone'       => $rootzoneMoisture,
                'date'           => $remoteSensing->image_date->format('d/m/Y'),
                'surface_status' => $smapService->classifySoilMoisture($surfaceMoisture),
                'rootzone_status'=> $smapService->classifySoilMoisture($rootzoneMoisture),
            ];

        } catch (\Exception $e) {
            $this->error = __('Error al cargar datos de humedad');
            logger()->error('SMAP data load failed', [
                'plot_id'   => $this->plot->id,
                'sigpac_id' => $this->sigpacId,
                'error'     => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function reloadData()
    {
        try {
            $this->loading = true;
            $this->error   = null;

            $service = app(\App\Services\RemoteSensing\NasaEarthdataService::class);
            $service->fetchEnrichedData($this->plot, includeArea: false, plotSigpacId: $this->sigpacId);

            $this->loadAvailableDates();
            $this->loadData();

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => __('Datos de humedad actualizados'),
            ]);

        } catch (\Exception $e) {
            $this->error = __('Error al actualizar datos de humedad');

            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('Error al actualizar datos de humedad'),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.smap-soil-card', [
            'smapData'       => $this->smapData,
            'error'          => $this->error,
            'availableDates' => $this->availableDates,
        ]);
    }
}
