<?php

namespace App\Jobs;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para actualizar datos NDVI de una parcela desde Sentinel Hub
 */
class UpdatePlotNdviJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de intentos
     */
    public int $tries = 3;

    /**
     * Tiempo de espera en segundos
     */
    public int $timeout = 120;

    /**
     * Backoff entre reintentos (segundos)
     */
    public int $backoff = 60;

    public function __construct(
        public Plot $plot
    ) {}

    /**
     * Ejecutar el job
     */
    public function handle(NasaEarthdataService $service): void
    {
        Log::info('Updating NDVI for plot', ['plot_id' => $this->plot->id, 'plot_name' => $this->plot->name]);

        try {
            // Obtener datos de Sentinel Hub
            $data = $service->fetchAndStoreNdvi($this->plot);

            if ($data) {
                Log::info('NDVI updated successfully', [
                    'plot_id' => $this->plot->id,
                    'ndvi' => $data->ndvi_mean,
                    'health_status' => $data->health_status,
                ]);
                // Alert is handled by PlotRemoteSensingObserver::saved()
            } else {
                Log::warning('No NDVI data returned', ['plot_id' => $this->plot->id]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update NDVI', [
                'plot_id' => $this->plot->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-lanzar para que el job reintente
        }
    }

    /**
     * Manejar fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('UpdatePlotNdviJob failed permanently', [
            'plot_id' => $this->plot->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
