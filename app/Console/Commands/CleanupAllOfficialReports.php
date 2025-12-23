<?php

namespace App\Console\Commands;

use App\Models\OfficialReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CleanupAllOfficialReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-all 
                            {--force : Forzar eliminación sin confirmación}
                            {--keep-pdfs : Mantener los archivos PDF en el storage}
                            {--user= : Limpiar solo informes de un usuario específico (ID)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los informes oficiales generados (útil para desarrollo/testing)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');
        $keepPdfs = $this->option('keep-pdfs');
        $force = $this->option('force');

        // Obtener informes a eliminar
        $query = OfficialReport::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("🔍 Filtrando informes del usuario ID: {$userId}");
        }

        $reports = $query->get();
        $totalReports = $reports->count();

        if ($totalReports === 0) {
            $this->info('✅ No hay informes para eliminar.');
            return Command::SUCCESS;
        }

        // Mostrar resumen
        $this->info('');
        $this->info('═══════════════════════════════════════');
        $this->info('📊 RESUMEN DE INFORMES A ELIMINAR');
        $this->info('═══════════════════════════════════════');
        $this->info("📄 Total de informes: {$totalReports}");
        
        // Agrupar por tipo
        $byType = $reports->groupBy('report_type');
        foreach ($byType as $type => $typeReports) {
            $this->info("   • {$type}: {$typeReports->count()}");
        }

        // Contar PDFs
        $pdfsCount = $reports->filter(fn($r) => $r->pdf_path)->count();
        $this->info("📎 PDFs asociados: {$pdfsCount}");
        $this->info('═══════════════════════════════════════');
        $this->info('');

        // Confirmación
        if (!$force) {
            if (!$this->confirm('¿Estás seguro de que deseas eliminar todos estos informes?', false)) {
                $this->warn('❌ Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        // Eliminar PDFs si no se especifica mantenerlos
        if (!$keepPdfs) {
            $this->info('🗑️  Eliminando archivos PDF...');
            $deletedPdfs = 0;
            $failedPdfs = 0;

            foreach ($reports as $report) {
                if ($report->pdf_path) {
                    try {
                        // Intentar eliminar usando Storage
                        if (!str_starts_with($report->pdf_path, storage_path())) {
                            if (Storage::disk('local')->exists($report->pdf_path)) {
                                Storage::disk('local')->delete($report->pdf_path);
                                $deletedPdfs++;
                            }
                        } else {
                            // Path absoluto
                            if (file_exists($report->pdf_path)) {
                                unlink($report->pdf_path);
                                $deletedPdfs++;
                            }
                        }
                    } catch (\Exception $e) {
                        $failedPdfs++;
                        $this->warn("⚠️  No se pudo eliminar PDF: {$report->pdf_path} - {$e->getMessage()}");
                    }
                }
            }

            $this->info("   ✅ PDFs eliminados: {$deletedPdfs}");
            if ($failedPdfs > 0) {
                $this->warn("   ⚠️  PDFs con error: {$failedPdfs}");
            }
        } else {
            $this->info('📎 Manteniendo archivos PDF (--keep-pdfs activado)');
        }

        // Eliminar registros de base de datos
        $this->info('');
        $this->info('🗑️  Eliminando registros de base de datos...');
        
        try {
            $deleted = DB::transaction(function () use ($query) {
                return $query->delete();
            });

            $this->info("   ✅ Registros eliminados: {$deleted}");
        } catch (\Exception $e) {
            $this->error("❌ Error al eliminar registros: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Resumen final
        $this->info('');
        $this->info('═══════════════════════════════════════');
        $this->info('✅ LIMPIEZA COMPLETADA');
        $this->info('═══════════════════════════════════════');
        $this->info("📄 Informes eliminados: {$totalReports}");
        if (!$keepPdfs) {
            $this->info("📎 PDFs eliminados: {$pdfsCount}");
        }
        $this->info('═══════════════════════════════════════');
        $this->info('');

        return Command::SUCCESS;
    }
}

