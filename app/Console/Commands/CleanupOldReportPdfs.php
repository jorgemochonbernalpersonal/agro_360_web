<?php

namespace App\Console\Commands;

use App\Models\OfficialReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldReportPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-pdfs 
                            {--days= : Override retention days from config}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old PDF files from official reports based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        // Obtener días de retención
        $retentionDays = $this->option('days') 
            ? (int) $this->option('days') 
            : config('reports.pdf_retention_days');

        if (!$retentionDays) {
            $this->error('PDF retention is disabled (set to null). No cleanup will be performed.');
            $this->info('To enable cleanup, set REPORTS_PDF_RETENTION_DAYS in .env or use --days option.');
            return 0;
        }

        $this->info("🗂️  Official Reports PDF Cleanup");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        // Calcular fecha límite
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $this->info("Retention policy: {$retentionDays} days");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->info("Mode: " . ($isDryRun ? 'DRY RUN (no files will be deleted)' : 'LIVE'));
        $this->newLine();

        // Buscar informes antiguos con PDFs
        $oldReports = OfficialReport::whereNotNull('pdf_path')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($oldReports->isEmpty()) {
            $this->info('✅ No old PDFs found to clean up.');
            return 0;
        }

        $totalSize = 0;
        $validFiles = collect();

        // Analizar archivos
        $this->info("Found {$oldReports->count()} reports with PDFs older than {$retentionDays} days:");
        $this->newLine();

        foreach ($oldReports as $report) {
            if (!$report->pdfExists()) {
                continue;
            }

            try {
                $size = Storage::disk('local')->size($report->pdf_path);
                $totalSize += $size;
                
                $validFiles->push([
                    'report' => $report,
                    'size' => $size,
                ]);
            } catch (\Exception $e) {
                $this->warn("  ⚠️  Could not access PDF for report #{$report->id}: {$e->getMessage()}");
            }
        }

        if ($validFiles->isEmpty()) {
            $this->info('✅ No valid PDFs found to clean up.');
            return 0;
        }

        // Mostrar resumen
        $this->table(
            ['Report ID', 'Type', 'Created', 'PDF Size', 'Age (days)'],
            $validFiles->take(10)->map(function ($item) use ($cutoffDate) {
                $report = $item['report'];
                return [
                    $report->id,
                    $report->report_type_name,
                    $report->created_at->format('Y-m-d'),
                    $this->formatBytes($item['size']),
                    $report->created_at->diffInDays(now()),
                ];
            })
        );

        if ($validFiles->count() > 10) {
            $this->info("... and " . ($validFiles->count() - 10) . " more");
        }

        $this->newLine();
        $this->info("Total files to clean: {$validFiles->count()}");
        $this->info("Total space to free: " . $this->formatBytes($totalSize));
        $this->newLine();

        // Confirmación
        if (!$isDryRun && !$force) {
            if (!$this->confirm('Do you want to proceed with the cleanup?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        // Realizar limpieza
        if ($isDryRun) {
            $this->info('🔍 DRY RUN - No files were deleted');
            return 0;
        }

        $deletedCount = 0;
        $failedCount = 0;

        $progressBar = $this->output->createProgressBar($validFiles->count());
        $progressBar->start();

        foreach ($validFiles as $item) {
            $report = $item['report'];
            
            try {
                // Eliminar archivo físico
                Storage::disk('local')->delete($report->pdf_path);
                
                // Actualizar registro en base de datos
                $report->update([
                    'pdf_path' => null,
                    'pdf_size' => null,
                    'pdf_filename' => null,
                ]);

                $deletedCount++;
                
                // Log
                Log::info('PDF cleanup: File deleted', [
                    'report_id' => $report->id,
                    'pdf_path' => $report->pdf_path,
                    'size' => $item['size'],
                    'age_days' => $report->created_at->diffInDays(now()),
                ]);
                
            } catch (\Exception $e) {
                $failedCount++;
                
                Log::error('PDF cleanup: Failed to delete file', [
                    'report_id' => $report->id,
                    'pdf_path' => $report->pdf_path,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen final
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ Cleanup completed!");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Files deleted: {$deletedCount}");
        $this->info("Failed: {$failedCount}");
        $this->info("Space freed: " . $this->formatBytes($totalSize));
        $this->newLine();

        if ($failedCount > 0) {
            $this->warn("⚠️  Some files could not be deleted. Check the logs for details.");
        }

        return 0;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
