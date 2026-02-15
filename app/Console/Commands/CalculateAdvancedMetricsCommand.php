<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Services\RemoteSensing\AdvancedAnalysisService;
use Illuminate\Console\Command;

class CalculateAdvancedMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remote-sensing:calculate-advanced-metrics
                            {--plot-id= : Specific plot ID to analyze}
                            {--force : Force recalculation ignoring cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate advanced remote sensing metrics (LAI, GNDVI, Maturity, Anomalies)';

    /**
     * Execute the console command.
     */
    public function handle(AdvancedAnalysisService $service): int
    {
        $this->info('🚀 Calculando métricas avanzadas de teledetección...');
        $this->newLine();

        $plotId = $this->option('plot-id');
        $force = $this->option('force');

        // Get plots to analyze
        $plots = $plotId
            ? Plot::where('id', $plotId)->get()
            : Plot::with('remoteSensingData')->get();

        if ($plots->isEmpty()) {
            $this->error('No se encontraron parcelas para analizar.');
            return self::FAILURE;
        }

        $this->info("📊 Analizando {$plots->count()} parcela(s)...");
        $this->newLine();

        $success = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($plots->count());
        $progressBar->start();

        foreach ($plots as $plot) {
            try {
                // Check if plot has remote sensing data
                if (!$plot->remoteSensingData()->exists()) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Perform analysis
                $analysis = $service->analyzeAdvanced($plot, $force);

                if (isset($analysis['error'])) {
                    $this->newLine(2);
                    $this->warn("⚠️  Parcela #{$plot->id} ({$plot->name}): {$analysis['error']}");
                    $skipped++;
                } else {
                    $success++;
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                $this->newLine(2);
                $this->error("❌ Error en parcela #{$plot->id} ({$plot->name}): {$e->getMessage()}");
                $errors++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Análisis completado');
        $this->newLine();
        
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['✅ Exitosas', $success],
                ['⚠️  Sin datos', $skipped],
                ['❌ Errores', $errors],
                ['📊 Total', $plots->count()],
            ]
        );

        // Show anomalies summary if any
        if ($success > 0) {
            $this->newLine();
            $this->info('🔍 Buscando anomalías críticas...');
            
            $criticalAnomalies = \App\Models\PlotRemoteSensing::where('anomaly_detected', true)
                ->whereIn('anomaly_severity', ['critical', 'high'])
                ->with('plot')
                ->latest('image_date')
                ->limit(10)
                ->get();

            if ($criticalAnomalies->isNotEmpty()) {
                $this->newLine();
                $this->warn("🚨 Se detectaron {$criticalAnomalies->count()} parcelas con anomalías críticas/altas:");
                $this->newLine();

                $anomalyData = $criticalAnomalies->map(fn($rs) => [
                    $rs->plot->id,
                    $rs->plot->name,
                    strtoupper($rs->anomaly_severity),
                    $rs->anomaly_type,
                    $rs->image_date->format('d/m/Y'),
                ])->toArray();

                $this->table(
                    ['ID', 'Parcela', 'Severidad', 'Tipo', 'Fecha'],
                    $anomalyData
                );
            } else {
                $this->info('✅ No se detectaron anomalías críticas.');
            }
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
