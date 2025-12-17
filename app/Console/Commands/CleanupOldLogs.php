<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=30 : Número de días de retención}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina archivos de log antiguos según la configuración de retención';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        $logsPath = storage_path('logs');
        
        $this->info("Limpiando logs anteriores a: {$cutoffDate->format('Y-m-d')} (retener {$days} días)");

        if (!File::exists($logsPath)) {
            $this->error("El directorio de logs no existe: {$logsPath}");
            return Command::FAILURE;
        }

        $deletedCount = 0;
        $totalSize = 0;

        // Obtener todos los archivos de log
        $logFiles = File::glob($logsPath . '/*.log');

        foreach ($logFiles as $file) {
            $fileInfo = File::lastModified($file);
            $fileDate = Carbon::createFromTimestamp($fileInfo);
            $fileSize = File::size($file);

            // Verificar si el archivo es más antiguo que el cutoff
            if ($fileDate->lt($cutoffDate)) {
                $this->line("Eliminando: " . basename($file) . " ({$fileDate->format('Y-m-d')}, " . $this->formatBytes($fileSize) . ")");
                
                if (File::delete($file)) {
                    $deletedCount++;
                    $totalSize += $fileSize;
                } else {
                    $this->warn("No se pudo eliminar: " . basename($file));
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("✅ Se eliminaron {$deletedCount} archivo(s) de log (" . $this->formatBytes($totalSize) . " liberados).");
        } else {
            $this->info("No se encontraron logs antiguos para eliminar.");
        }

        return Command::SUCCESS;
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
