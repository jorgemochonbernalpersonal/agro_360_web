<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use Livewire\Component;

class SpectralBandsCard extends Component
{
    public Plot $plot;
    public ?int $sigpacId = null;
    public ?array $spectralData = null;
    public ?array $indices = null;
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
        $query = $this->plot->remoteSensingData()->whereNotNull('ndvi_mean');
        if ($this->sigpacId) {
            $query->where('multipart_plot_sigpac_id', $this->sigpacId);
        }
        $dates = $query->orderBy('image_date', 'desc')->limit(30)
            ->pluck('image_date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

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

            $query = $this->plot->remoteSensingData()->whereNotNull('ndvi_mean');
            if ($this->sigpacId) {
                $query->where('multipart_plot_sigpac_id', $this->sigpacId);
            }
            if ($this->selectedDate) {
                $query->whereDate('image_date', $this->selectedDate);
            }
            $remoteSensing = $query->orderBy('image_date', 'desc')->first();

            if (!$remoteSensing) {
                $this->error = 'Sin datos para este recinto. Haz clic en "Actualizar Sentinel-2" para cargarlos.';
                return;
            }

            $metadata = $remoteSensing->metadata ?? [];
            $this->spectralData = [
                'date'              => $remoteSensing->image_date->format('d/m/Y'),
                'satellite'         => $remoteSensing->satellite ?? 'Sentinel-2',
                'valid_pixel_ratio' => $metadata['valid_pixel_ratio'] ?? null,
                'red_band'          => $remoteSensing->red_band,
                'nir_band'          => $remoteSensing->nir_band,
                'green_band'        => $remoteSensing->green_band,
            ];

            // Índices derivados de bandas espectrales Sentinel-2
            $this->indices = [
                'ndvi' => [
                    'value'       => $remoteSensing->ndvi_mean,
                    'label'       => 'NDVI',
                    'description' => 'Vigor vegetativo general',
                    'color'       => 'green',
                ],
                'gndvi' => [
                    'value'       => $remoteSensing->gndvi,
                    'label'       => 'GNDVI',
                    'description' => 'Contenido de nitrógeno/clorofila',
                    'color'       => 'emerald',
                ],
                'ndwi' => [
                    'value'       => $remoteSensing->ndwi_mean,
                    'label'       => 'NDWI',
                    'description' => 'Contenido de agua en vegetación',
                    'color'       => 'blue',
                ],
                'ndre' => [
                    'value'       => $remoteSensing->ndre,
                    'label'       => 'NDRE',
                    'description' => 'Clorofila sin saturación (Red-Edge)',
                    'color'       => 'lime',
                ],
            ];

        } catch (\Exception $e) {
            $this->error = 'Error al cargar datos espectrales';
            logger()->error('Spectral data load failed', [
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
            'message' => 'Datos espectrales actualizados',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.spectral-bands-card', [
            'spectralData' => $this->spectralData,
            'indices' => $this->indices,
            'error' => $this->error,
            'availableDates' => $this->availableDates,
        ]);
    }
}
