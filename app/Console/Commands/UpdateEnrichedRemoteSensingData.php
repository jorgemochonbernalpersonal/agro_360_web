<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Console\Command;

class UpdateEnrichedRemoteSensingData extends Command
{
    protected $signature = 'remote-sensing:update-enriched 
                            {--plot-id= : Update specific plot ID}
                            {--include-area : Include area statistics (slower, async)}
                            {--ultra : Use ALL NASA products (VIIRS, LAI, SMAP, ET)}
                            {--force : Force update even if recently updated}';

    protected $description = 'Update enriched remote sensing data (NDVI + LST + optionally ALL NASA products)';

    private NasaEarthdataService $service;

    public function __construct(NasaEarthdataService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $ultra = $this->option('ultra');
        
        if ($ultra) {
            $this->info('🌌 Updating ULTRA-ENRICHED Remote Sensing Data...');
            $this->warn('   Including: VIIRS + Spectral Bands + LAI + LST + SMAP + ET');
        } else {
            $this->info('🛰️ Updating Enriched Remote Sensing Data...');
        }
        
        $this->newLine();

        // Get plots
        if ($plotId = $this->option('plot-id')) {
            $plots = Plot::where('id', $plotId)->get();
            
            if ($plots->isEmpty()) {
                $this->error("❌ Plot ID {$plotId} not found");
                return Command::FAILURE;
            }
        } else {
            $plots = Plot::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();
        }

        if ($plots->isEmpty()) {
            $this->warn('⚠️ No plots with coordinates found');
            return Command::SUCCESS;
        }

        $this->info("📍 Found {$plots->count()} plot(s) to update");
        $this->newLine();

        $includeArea = $this->option('include-area');
        
        if ($includeArea) {
            $this->warn('⚠️ Area statistics enabled - requests are async and may take 5-10 minutes to process');
            $this->newLine();
        }

        $progressBar = $this->output->createProgressBar($plots->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($plots as $plot) {
            $progressBar->setMessage("Processing: {$plot->name}");
            
            try {
                // Check if recently updated (unless forced)
                if (!$this->option('force')) {
                    $latest = $plot->remoteSensingData()
                        ->latest('image_date')
                        ->first();
                    
                    if ($latest && $latest->image_date->isToday()) {
                        // Check if ultra data exists if ultra mode
                        if ($ultra && !$latest->fpar) {
                            // Continue to update with ultra data
                        } else {
                            $skipped++;
                            $progressBar->advance();
                            continue;
                        }
                    }
                }

                // Fetch data (ultra or standard enriched)
                if ($ultra) {
                    $result = $this->service->fetchUltraEnrichedData($plot, $includeArea);
                } else {
                    $result = $this->service->fetchEnrichedData($plot, $includeArea);
                }

                if ($result) {
                    $success++;
                    
                    if ($includeArea && isset($result->metadata['area_task_id'])) {
                        $this->newLine();
                        $this->info("  ✅ {$plot->name} - Area task created: {$result->metadata['area_task_id']}");
                    }
                } else {
                    $failed++;
                }

            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("  ❌ {$plot->name}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📊 Update Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Success', $success],
                ['⏭️ Skipped (already updated today)', $skipped],
                ['❌ Failed', $failed],
                ['📁 Total', $plots->count()],
            ]
        );

        if ($includeArea && $success > 0) {
            $this->newLine();
            $this->warn('⏱️ Area statistics are processing in background.');
            $this->info('   Check back in 5-10 minutes or run:');
            $this->line('   php artisan remote-sensing:check-area-tasks');
        }

        if ($ultra && $success > 0) {
            $this->newLine();
            $this->info('🌟 Ultra-enriched data includes:');
            $this->line('   ✓ VIIRS NDVI (375m resolution)');
            $this->line('   ✓ Real spectral bands (GNDVI, NDRE, MSR, CI-green, ARVI)');
            $this->line('   ✓ Official LAI + FPAR from MODIS');
            $this->line('   ✓ LST (Land Surface Temperature)');
            $this->line('   ✓ SMAP Soil Moisture (satellite)');
            $this->line('   ✓ NASA Official Evapotranspiration');
        }

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}

