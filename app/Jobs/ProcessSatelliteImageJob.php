<?php

namespace App\Jobs;

use App\Models\Plot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSatelliteImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Plot $plot,
        public string $imageDate
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing satellite image', [
            'plot_id' => $this->plot->id,
            'image_date' => $this->imageDate,
        ]);

        try {
            // TODO: Implementar integración con servicio de teledetección
            // Por ejemplo: Sentinel Hub, Planet, etc.

            // Simulación de llamada a API
            $indices = $this->calculateVegetationIndices();

            // Guardar datos de teledetección
            $this->plot->remoteSensingData()->create([
                'image_date' => $this->imageDate,
                'ndvi' => $indices['ndvi'],
                'evi' => $indices['evi'],
                'savi' => $indices['savi'],
                'ndwi' => $indices['ndwi'],
                'cloud_coverage' => $indices['cloud_coverage'],
            ]);

            // NDVI alert is handled by PlotRemoteSensingObserver::saved()

            Log::info('Satellite image processed successfully', [
                'plot_id' => $this->plot->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process satellite image', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate vegetation indices (simulación)
     */
    protected function calculateVegetationIndices(): array
    {
        // En producción, estos valores vendrían de la API de teledetección
        return [
            'ndvi' => round(rand(30, 90) / 100, 3),
            'evi' => round(rand(20, 80) / 100, 3),
            'savi' => round(rand(25, 85) / 100, 3),
            'ndwi' => round(rand(-30, 30) / 100, 3),
            'cloud_coverage' => round(rand(0, 20) / 100, 2),
        ];
    }
}
