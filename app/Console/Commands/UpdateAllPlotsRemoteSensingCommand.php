<?php

namespace App\Console\Commands;

use App\Jobs\UpdatePlotNdviJob;
use App\Models\Plot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Update remote sensing data for all active plots
 * Typically run via scheduled task (cron)
 */
class UpdateAllPlotsRemoteSensingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'remote-sensing:update-all
                            {--limit= : Limit number of plots to update}
                            {--queue= : Queue name to use (default: default)}
                            {--delay= : Delay between jobs in seconds (default: 0)}';

    /**
     * The console command description.
     */
    protected $description = 'Update remote sensing data for all active plots';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🛰️  Starting remote sensing update for all plots...');
        $this->newLine();

        // Get all plots with active subscriptions
        $query = Plot::whereHas('viticulturist', function ($q) {
            $q->whereHas('subscription', function ($sq) {
                $sq->where('status', 'active')
                    ->where('ends_at', '>', now());
            });
        })->orderBy('id');

        // Apply limit if specified
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $plots = $query->get();

        if ($plots->isEmpty()) {
            $this->warn('No plots found with active subscriptions.');
            return self::SUCCESS;
        }

        $this->info("Found {$plots->count()} plots to update");
        $this->newLine();

        $bar = $this->output->createProgressBar($plots->count());
        $bar->start();

        $queued = 0;
        $skipped = 0;
        $queueName = $this->option('queue') ?? 'default';
        $delay = (int) ($this->option('delay') ?? 0);

        foreach ($plots as $plot) {
            try {
                // Dispatch job to queue
                UpdatePlotNdviJob::dispatch($plot)
                    ->onQueue($queueName)
                    ->delay(now()->addSeconds($delay * $queued));
                
                $queued++;
            } catch (\Exception $e) {
                $skipped++;
                Log::error('Failed to queue plot update', [
                    'plot_id' => $plot->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Update jobs queued successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total plots', $plots->count()],
                ['Jobs queued', $queued],
                ['Skipped', $skipped],
                ['Queue', $queueName],
                ['Delay per job', $delay . 's'],
            ]
        );

        Log::info('Remote sensing update queued', [
            'total' => $plots->count(),
            'queued' => $queued,
            'skipped' => $skipped,
            'queue' => $queueName,
        ]);

        return self::SUCCESS;
    }
}
