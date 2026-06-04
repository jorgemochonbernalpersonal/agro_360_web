<?php

namespace App\Console\Commands;

use App\Models\PlotRemoteSensing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Clean old remote sensing data to keep database lean
 */
class CleanOldRemoteSensingDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'remote-sensing:clean-old-data
                            {--days=365 : Keep data from last N days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean remote sensing data older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🗑️  Cleaning remote sensing data older than {$days} days...");
        $this->newLine();

        $cutoffDate = now()->subDays($days);

        // Count records to delete
        $count = PlotRemoteSensing::where('image_date', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('✅ No old data to clean');

            return self::SUCCESS;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Cutoff date', $cutoffDate->format('Y-m-d')],
                ['Records to delete', number_format($count)],
                ['Mode', $dryRun ? 'DRY RUN' : 'LIVE'],
            ]
        );

        if ($dryRun) {
            $this->warn('🔍 DRY RUN - No data will be deleted');

            return self::SUCCESS;
        }

        if (! $this->confirm('Are you sure you want to delete this data?', false)) {
            $this->info('Operation cancelled');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Deleting old records...');

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        $chunkSize = 1000;

        PlotRemoteSensing::where('image_date', '<', $cutoffDate)
            ->chunkById($chunkSize, function ($records) use ($bar, &$deleted) {
                foreach ($records as $record) {
                    $record->delete();
                    $deleted++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Deleted {$deleted} old records");

        Log::info('Old remote sensing data cleaned', [
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'deleted' => $deleted,
        ]);

        return self::SUCCESS;
    }
}
