<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RegenerateMockRemoteSensingDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remote-sensing:regenerate-mock
                            {--plot-id= : Specific plot ID to regenerate, or all if not provided}
                            {--clear : Clear existing data before regenerating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate mock remote sensing data for plots';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('services.nasa_earthdata.mock')) {
            $this->error('Mock mode is disabled. Enable NASA_EARTHDATA_MOCK=true in .env');

            return self::FAILURE;
        }

        $plotId = $this->option('plot-id');
        $clear = $this->option('clear');

        $plots = $plotId
            ? Plot::where('id', $plotId)->get()
            : Plot::all();

        if ($plots->isEmpty()) {
            $this->error('No plots found.');

            return self::FAILURE;
        }

        $service = app(NasaEarthdataService::class);
        $bar = $this->output->createProgressBar($plots->count());
        $bar->start();

        foreach ($plots as $plot) {
            // Clear existing data if requested
            if ($clear) {
                PlotRemoteSensing::where('plot_id', $plot->id)->delete();
                $this->info("\nCleared existing data for plot: {$plot->name}");
            }

            // Clear cache
            Cache::forget("ndvi_latest_{$plot->id}");
            Cache::forget("ndvi_historical_{$plot->id}");

            // Generate new data
            $service->getLatestData($plot, true);
            $service->getHistoricalData($plot, 365);

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info('✅ Mock remote sensing data regenerated successfully!');
        $this->info("Processed {$plots->count()} plots");

        return self::SUCCESS;
    }
}
