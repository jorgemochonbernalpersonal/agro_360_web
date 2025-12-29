<?php

namespace App\Jobs;

use App\Models\Plot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job que recorre todas las parcelas activas y programa la actualización de NDVI
 * 
 * Se ejecuta semanalmente via scheduler
 */
class UpdateAllPlotsNdviJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tiempo de espera en segundos
     */
    public int $timeout = 600;

    /**
     * Ejecutar el job
     */
    public function handle(): void
    {
        Log::info('Starting batch NDVI update for all plots');

        // Obtener todas las parcelas activas
        $plots = Plot::where('active', true)
            ->whereHas('multipartCoordinates', function ($query) {
                $query->whereNotNull('plot_geometry_id');
            })
            ->get();

        $count = $plots->count();
        Log::info("Found {$count} plots with geometries to update");

        $dispatched = 0;
        $delaySeconds = 0;

        foreach ($plots as $plot) {
            // Espaciar los jobs para no saturar la API
            // 5 segundos entre cada petición = 720 parcelas/hora
            UpdatePlotNdviJob::dispatch($plot)
                ->delay(now()->addSeconds($delaySeconds))
                ->onQueue('remote-sensing');

            $delaySeconds += 5; // 5 segundos entre jobs
            $dispatched++;
        }

        Log::info("Dispatched {$dispatched} NDVI update jobs", [
            'estimated_completion_minutes' => round($delaySeconds / 60, 1),
        ]);
    }

    /**
     * Manejar fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateAllPlotsNdviJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
