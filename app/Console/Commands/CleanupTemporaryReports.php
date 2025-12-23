<?php

namespace App\Console\Commands;

use App\Models\OfficialReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupTemporaryReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-temp {--force : Eliminar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina informes oficiales con signature_hash temporal (temp) que quedaron huérfanos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando informes con signature_hash temporal...');

        // Buscar todos los registros con signature_hash = 'temp'
        $tempReports = OfficialReport::where('signature_hash', 'temp')->get();

        if ($tempReports->isEmpty()) {
            $this->info('✅ No se encontraron informes con signature_hash temporal.');
            return Command::SUCCESS;
        }

        $count = $tempReports->count();
        $this->warn("⚠️  Se encontraron {$count} informe(s) con signature_hash temporal:");

        // Mostrar información de los registros
        $this->table(
            ['ID', 'Usuario', 'Tipo', 'Periodo', 'Creado', 'PDF'],
            $tempReports->map(function ($report) {
                return [
                    $report->id,
                    $report->user->name ?? "ID: {$report->user_id}",
                    $report->report_type_name,
                    $report->period_start->format('Y-m-d') . ' / ' . $report->period_end->format('Y-m-d'),
                    $report->created_at->format('Y-m-d H:i:s'),
                    $report->pdfExists() ? '✅ Sí' : '❌ No',
                ];
            })->toArray()
        );

        // Confirmar eliminación
        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas eliminar estos informes? Esto también eliminará los PDFs asociados si existen.', false)) {
                $this->info('Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        $deletedCount = 0;
        $pdfDeletedCount = 0;
        $errors = [];

        foreach ($tempReports as $report) {
            try {
                // Eliminar PDF si existe
                if ($report->pdfExists() && $report->pdf_path) {
                    try {
                        if (!str_starts_with($report->pdf_path, storage_path())) {
                            Storage::disk('local')->delete($report->pdf_path);
                        } else {
                            if (file_exists($report->pdf_path)) {
                                unlink($report->pdf_path);
                            }
                        }
                        $pdfDeletedCount++;
                        $this->line("  ✓ PDF eliminado: {$report->pdf_filename}");
                    } catch (\Exception $e) {
                        $this->warn("  ⚠ No se pudo eliminar PDF: {$e->getMessage()}");
                    }
                }

                // Eliminar registro
                $reportId = $report->id;
                $report->delete();
                $deletedCount++;
                $this->line("  ✓ Informe eliminado: ID {$reportId}");

            } catch (\Exception $e) {
                $errors[] = "ID {$report->id}: {$e->getMessage()}";
                $this->error("  ✗ Error al eliminar informe ID {$report->id}: {$e->getMessage()}");
            }
        }

        // Resumen
        $this->newLine();
        if ($deletedCount > 0) {
            $this->info("✅ Se eliminaron {$deletedCount} informe(s).");
            if ($pdfDeletedCount > 0) {
                $this->info("✅ Se eliminaron {$pdfDeletedCount} archivo(s) PDF.");
            }
        }

        if (!empty($errors)) {
            $this->warn("⚠️  Se encontraron " . count($errors) . " error(es) durante la eliminación:");
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        // Log de auditoría
        Log::info('Comando cleanup-temp ejecutado', [
            'deleted_reports' => $deletedCount,
            'deleted_pdfs' => $pdfDeletedCount,
            'errors' => count($errors),
            'executed_by' => 'console',
        ]);

        return Command::SUCCESS;
    }
}

