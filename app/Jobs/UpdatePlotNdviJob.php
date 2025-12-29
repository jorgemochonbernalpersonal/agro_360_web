<?php

namespace App\Jobs;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Notifications\NdviAlertNotification;
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

                // Verificar si hay alerta (NDVI bajo)
                $this->checkForAlerts($data);
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
     * Verificar si se debe generar una alerta
     */
    private function checkForAlerts(PlotRemoteSensing $data): void
    {
        // Si el estado es "poor" o "critical", generar alerta
        if (in_array($data->health_status, ['poor', 'critical'])) {
            // Obtener el dato anterior para comparar
            $previousData = PlotRemoteSensing::where('plot_id', $this->plot->id)
                ->where('id', '<', $data->id)
                ->orderBy('image_date', 'desc')
                ->first();

            // Si empeoró significativamente, crear notificación
            if ($previousData && $data->ndvi_mean < $previousData->ndvi_mean - 0.1) {
                $this->createAlert($data, $previousData);
            }
        }
    }

    /**
     * Crear notificación de alerta
     */
    private function createAlert(PlotRemoteSensing $current, PlotRemoteSensing $previous): void
    {
        $viticulturist = $this->plot->viticulturist;
        
        if (!$viticulturist) {
            return;
        }

        // Calcular el cambio porcentual
        $change = (($current->ndvi_mean - $previous->ndvi_mean) / $previous->ndvi_mean) * 100;

        // Crear notificación
        $viticulturist->notifications()->create([
            'type' => 'App\\Notifications\\NdviAlertNotification',
            'data' => [
                'title' => '⚠️ Alerta NDVI - ' . $this->plot->name,
                'message' => sprintf(
                    'El NDVI de "%s" ha bajado un %.1f%% (de %.2f a %.2f). Estado: %s',
                    $this->plot->name,
                    abs($change),
                    $previous->ndvi_mean,
                    $current->ndvi_mean,
                    $current->health_text
                ),
                'plot_id' => $this->plot->id,
                'plot_name' => $this->plot->name,
                'ndvi_current' => $current->ndvi_mean,
                'ndvi_previous' => $previous->ndvi_mean,
                'change_percent' => $change,
                'health_status' => $current->health_status,
                'url' => route('plots.show', $this->plot),
            ],
        ]);

        Log::info('NDVI alert created', [
            'plot_id' => $this->plot->id,
            'user_id' => $viticulturist->id,
            'change' => $change,
        ]);
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
