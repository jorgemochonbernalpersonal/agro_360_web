<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAllPlotsNdviJob;
use App\Jobs\UpdatePlotNdviJob;
use App\Models\Plot;
use App\Services\RemoteSensing\SentinelHubService;
use Illuminate\Console\Command;

/**
 * Comando para actualizar manualmente los datos NDVI
 */
class UpdateNdviCommand extends Command
{
    /**
     * Nombre y argumentos del comando
     */
    protected $signature = 'ndvi:update 
                            {--plot= : ID de una parcela específica}
                            {--all : Actualizar todas las parcelas}
                            {--sync : Ejecutar síncronamente (sin cola)}';

    /**
     * Descripción del comando
     */
    protected $description = 'Actualizar datos NDVI desde Sentinel Hub';

    /**
     * Ejecutar el comando
     */
    public function handle(SentinelHubService $service): int
    {
        $plotId = $this->option('plot');
        $all = $this->option('all');
        $sync = $this->option('sync');

        if ($plotId) {
            return $this->updateSinglePlot((int) $plotId, $service, $sync);
        }

        if ($all) {
            return $this->updateAllPlots($sync);
        }

        $this->error('Debes especificar --plot=ID o --all');
        $this->line('');
        $this->line('Ejemplos:');
        $this->line('  php artisan ndvi:update --plot=123        # Actualizar parcela específica');
        $this->line('  php artisan ndvi:update --all             # Actualizar todas (en cola)');
        $this->line('  php artisan ndvi:update --all --sync      # Actualizar todas (síncrono)');

        return self::FAILURE;
    }

    /**
     * Actualizar una sola parcela
     */
    private function updateSinglePlot(int $plotId, SentinelHubService $service, bool $sync): int
    {
        /** @var Plot|null $plot */
        $plot = Plot::find($plotId);

        if (! $plot) {
            $this->error("No se encontró la parcela con ID {$plotId}");

            return self::FAILURE;
        }

        $this->info("Actualizando NDVI de: {$plot->name}");

        if ($sync) {
            $data = $service->fetchAndStoreNdvi($plot);

            if ($data) {
                $this->info("✅ NDVI actualizado: {$data->ndvi_mean} ({$data->health_text})");
            } else {
                $this->warn('⚠️ No se pudo obtener datos NDVI');
            }
        } else {
            UpdatePlotNdviJob::dispatch($plot)->onQueue('remote-sensing');
            $this->info('📤 Job encolado para actualización');
        }

        return self::SUCCESS;
    }

    /**
     * Actualizar todas las parcelas
     */
    private function updateAllPlots(bool $sync): int
    {
        $plots = Plot::where('active', true)
            ->whereHas('multipartCoordinates', function ($query) {
                $query->whereNotNull('plot_geometry_id');
            })
            ->get();

        $count = $plots->count();
        $this->info("Encontradas {$count} parcelas con geometrías");

        if ($count === 0) {
            $this->warn('No hay parcelas para actualizar');

            return self::SUCCESS;
        }

        if ($sync) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $service = app(SentinelHubService::class);
            $success = 0;
            $failed = 0;

            foreach ($plots as $plot) {
                try {
                    $data = $service->fetchAndStoreNdvi($plot);
                    $data ? $success++ : $failed++;
                } catch (\Exception $e) {
                    $failed++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Completado: {$success} éxito, {$failed} fallos");
        } else {
            UpdateAllPlotsNdviJob::dispatch()->onQueue('remote-sensing');
            $this->info("📤 Job encolado para actualizar {$count} parcelas");
            $this->line('Los jobs se procesarán espaciadamente para no saturar la API');
        }

        return self::SUCCESS;
    }
}
