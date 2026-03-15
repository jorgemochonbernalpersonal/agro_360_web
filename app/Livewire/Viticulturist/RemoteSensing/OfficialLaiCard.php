<?php

namespace App\Livewire\Viticulturist\RemoteSensing;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaLAIService;
use Livewire\Component;

class OfficialLaiCard extends Component
{
    public Plot $plot;
    public ?int $sigpacId = null;
    public ?array $laiData = null;
    public ?array $fparData = null;
    public ?array $yieldEstimate = null;
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

            $laiService = app(NasaLAIService::class);

            // Estimar LAI desde NDVI (fórmula empírica para viñedo: LAI = -ln(1 - NDVI) * 0.9)
            $ndvi = (float) ($remoteSensing->ndvi_mean ?? 0);
            $estimatedLai = $ndvi > 0 ? round(-log(max(0.01, 1 - min(0.99, $ndvi))) * 0.9, 2) : 0;

            $lai = $remoteSensing->lai ?? $estimatedLai;
            $classification = $laiService->classifyLAI($lai);

            $this->laiData = array_merge([
                'value'  => $lai,
                'date'   => $remoteSensing->image_date->format('d/m/Y'),
                'source' => $remoteSensing->lai ? 'NASA MODIS (Oficial)' : 'Estimado desde Sentinel-2 NDVI',
            ], $classification);

            $areaHa = $this->plot->area ?? 1;
            $this->yieldEstimate = $laiService->estimateYield($lai, $areaHa, 'red');

            $fpar = $remoteSensing->fpar ?? round($ndvi * 0.95, 3);
            $this->fparData = $laiService->analyzeFPAR($fpar);

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
