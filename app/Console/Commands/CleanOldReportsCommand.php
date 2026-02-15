<?php

namespace App\Console\Commands;

use App\Models\OfficialReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:clean-old {--days=90 : Number of days to keep reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old generated reports and their PDF files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning reports older than {$days} days (before {$cutoffDate->toDateString()})...");

        $oldReports = OfficialReport::where('created_at', '<', $cutoffDate)->get();

        $deletedCount = 0;
        $failedCount = 0;

        $this->withProgressBar($oldReports, function ($report) use (&$deletedCount, &$failedCount) {
            try {
                // Eliminar archivo PDF si existe
                if ($report->pdf_path && Storage::exists($report->pdf_path)) {
                    Storage::delete($report->pdf_path);
                }

                // Eliminar registro de base de datos
                $report->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to delete report {$report->id}: {$e->getMessage()}");
                $failedCount++;
            }
        });

        $this->newLine(2);
        $this->info("✅ Successfully deleted {$deletedCount} old reports.");
        
        if ($failedCount > 0) {
            $this->warn("⚠️  Failed to delete {$failedCount} reports.");
        }

        return self::SUCCESS;
    }
}
