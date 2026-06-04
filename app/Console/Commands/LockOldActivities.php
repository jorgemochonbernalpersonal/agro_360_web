<?php

namespace App\Console\Commands;

use App\Models\AgriculturalActivity;
use Illuminate\Console\Command;

class LockOldActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:lock-old 
                            {--days=7 : Número de días después de los cuales bloquear actividades}
                            {--dry-run : Simular sin realizar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bloquear actividades agrícolas antiguas para prevenir modificaciones (cumplimiento PAC)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🔒 Bloqueando actividades con más de {$days} días...");

        if ($dryRun) {
            $this->warn('⚠️  MODO SIMULACIÓN - No se realizarán cambios');
        }

        // Buscar actividades desbloqueadas con más de X días
        $cutoffDate = now()->subDays($days);

        $activities = AgriculturalActivity::where('is_locked', false)
            ->where('activity_date', '<=', $cutoffDate)
            ->get();

        if ($activities->isEmpty()) {
            $this->info('✅ No hay actividades para bloquear.');

            return 0;
        }

        $this->info("📊 Encontradas {$activities->count()} actividades para bloquear:");

        // Mostrar tabla con resumen
        $tableData = [];
        foreach ($activities as $activity) {
            $tableData[] = [
                'ID' => $activity->id,
                'Fecha' => $activity->activity_date->format('d/m/Y'),
                'Tipo' => $activity->activity_type,
                'Parcela' => $activity->plot?->name ?? 'N/A',
                'Días' => $activity->activity_date->diffInDays(now()),
            ];
        }

        $this->table(
            ['ID', 'Fecha', 'Tipo', 'Parcela', 'Días Antigüedad'],
            $tableData
        );

        if ($dryRun) {
            $this->info('✅ Simulación completada. Usa el comando sin --dry-run para aplicar cambios.');

            return 0;
        }

        // Confirmar antes de bloquear
        if (! $this->confirm('¿Deseas bloquear estas actividades?', true)) {
            $this->warn('❌ Operación cancelada.');

            return 0;
        }

        // Bloquear actividades
        $locked = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($activities->count());
        $progressBar->start();

        foreach ($activities as $activity) {
            try {
                $activity->lock(1); // Sistema (user_id = 1)
                $locked++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Error bloqueando actividad #{$activity->id}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('✅ Proceso completado:');
        $this->info("   - Bloqueadas: {$locked}");

        if ($errors > 0) {
            $this->error("   - Errores: {$errors}");
        }

        return 0;
    }
}
