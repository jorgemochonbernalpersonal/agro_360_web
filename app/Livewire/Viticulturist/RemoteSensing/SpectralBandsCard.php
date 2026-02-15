<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use Livewire\Component;

class SpectralBandsCard extends Component
{
    public Plot $plot;
    public ?array $spectralData = null;
    public ?array $indices = null;
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
                ->whereNotNull('red_band')
                ->latest('image_date')
                ->first();

            if (!$remoteSensing) {
                $this->error = 'No hay datos de bandas espectrales disponibles';
                return;
            }

            // Bandas espectrales
            $this->spectralData = [
                'red' => $remoteSensing->red_band,
                'nir' => $remoteSensing->nir_band,
                'green' => $remoteSensing->green_band,
                'blue' => $remoteSensing->blue_band,
                'date' => $remoteSensing->image_date->format('d/m/Y'),
                'satellite' => $remoteSensing->satellite ?? 'VIIRS',
            ];

            // Índices vegetación (REALES, no mocks)
            $this->indices = [
                'ndvi' => [
                    'value' => $remoteSensing->ndvi_mean,
                    'label' => 'NDVI',
                    'description' => 'Vigor vegetativo general',
                    'color' => 'green',
                ],
                'gndvi' => [
                    'value' => $remoteSensing->gndvi,
                    'label' => 'GNDVI',
                    'description' => 'Contenido de nitrógeno/clorofila',
                    'color' => 'emerald',
                    'is_real' => true,
                ],
                'ndre' => [
                    'value' => $remoteSensing->ndre,
                    'label' => 'NDRE',
                    'description' => 'Clorofila (sin saturación)',
                    'color' => 'lime',
                    'is_real' => true,
                ],
                'msr' => [
                    'value' => $remoteSensing->msr,
                    'label' => 'MSR',
                    'description' => 'Biomasa',
                    'color' => 'teal',
                ],
                'ci_green' => [
                    'value' => $remoteSensing->ci_green,
                    'label' => 'CI-green',
                    'description' => 'Índice de clorofila',
                    'color' => 'cyan',
                ],
                'arvi' => [
                    'value' => $remoteSensing->arvi,
                    'label' => 'ARVI',
                    'description' => 'Resistente a atmósfera',
                    'color' => 'indigo',
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

    public function refresh()
    {
        $this->loadData();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos espectrales actualizados',
        ]);
    }

    public function render()
    {
        return view('livewire.viticulturist.remote-sensing.spectral-bands-card');
    }
}
