<?php

namespace App\Console\Commands;

use App\Jobs\UpdatePlotSentinel2Job;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Update remote sensing data for all active plots.
 * Dispatches one job per sigpac parcel (MultipartPlotSigpac with geometry),
 * so each SIGPAC polygon is analysed independently via Sentinel-2.
 */
class UpdateAllPlotsRemoteSensingCommand extends Command
{
    protected $signature = 'remote-sensing:update-all
                            {--limit= : Limit number of sigpac parcels to update}
                            {--queue= : Queue name to use (default: default)}
                            {--delay= : Delay between jobs in seconds (default: 0)}';

    protected $description = 'Update Sentinel-2 remote sensing data for all sigpac parcels of active plots';

    public function handle(): int
    {
        $this->info('🛰️  Starting Sentinel-2 update for all sigpac parcels...');
        $this->newLine();

        // One job per sigpac parcel (multipart_plot_sigpac row with geometry) for plots with active subs
        $query = MultipartPlotSigpac::whereNotNull('plot_geometry_id')
            ->whereHas('plot.viticulturist.subscription', function ($sq) {
                $sq->where('status', 'active')->where('ends_at', '>', now());
            })
            ->with('plot')
            ->orderBy('plot_id')
            ->orderBy('id');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $plotSigpacs = $query->get();

        if ($plotSigpacs->isEmpty()) {
            $this->warn('No sigpac parcels found for plots with active subscriptions.');
            return self::SUCCESS;
        }

        $this->info("Found {$plotSigpacs->count()} sigpac parcels to update");
        $this->newLine();

        $bar      = $this->output->createProgressBar($plotSigpacs->count());
        $bar->start();

        $queued    = 0;
        $skipped   = 0;
        $queueName = $this->option('queue') ?? 'default';
        $delay     = (int) ($this->option('delay') ?? 0);

        foreach ($plotSigpacs as $plotSigpac) {
            try {
                UpdatePlotSentinel2Job::dispatch($plotSigpac->plot, $plotSigpac->id)
                    ->onQueue($queueName)
                    ->delay(now()->addSeconds($delay * $queued));

                $queued++;
            } catch (\Exception $e) {
                $skipped++;
                Log::error('Failed to queue sigpac parcel update', [
                    'plot_id'        => $plotSigpac->plot_id,
                    'plot_sigpac_id' => $plotSigpac->id,
                    'error'          => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Update jobs queued successfully!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total sigpac parcels', $plotSigpacs->count()],
                ['Jobs queued',          $queued],
                ['Skipped',              $skipped],
                ['Queue',                $queueName],
                ['Delay per job',        $delay . 's'],
            ]
        );

        Log::info('Remote sensing update queued (per sigpac parcel)', [
            'total'   => $plotSigpacs->count(),
            'queued'  => $queued,
            'skipped' => $skipped,
            'queue'   => $queueName,
        ]);

        return self::SUCCESS;
    }
}
