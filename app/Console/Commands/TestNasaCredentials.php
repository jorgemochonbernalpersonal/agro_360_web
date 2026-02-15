<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Console\Command;

class TestNasaCredentials extends Command
{
    protected $signature = 'remote-sensing:test-credentials';
    protected $description = 'Test NASA Earthdata credentials and fetch real data';

    public function handle(): int
    {
        $this->info('🔍 Testing NASA Earthdata credentials...');
        $this->newLine();

        // Check configuration
        $mock = config('services.nasa_earthdata.mock');
        $username = config('services.nasa_earthdata.username');
        
        $this->info("Configuration:");
        $this->line("  Mock mode: " . ($mock ? 'ENABLED' : 'DISABLED'));
        $this->line("  Username: " . ($username ?: 'NOT SET'));
        $this->line("  Environment: " . config('app.env'));
        $this->newLine();

        if ($mock) {
            $this->warn('⚠️  Mock mode is ENABLED. Real API will not be tested.');
            $this->info('To test real credentials, set: NASA_EARTHDATA_MOCK=false in .env');
            $this->newLine();
        }

        // Get first plot
        $plot = Plot::first();
        
        if (!$plot) {
            $this->error('❌ No plots found in database. Create a plot first.');
            return self::FAILURE;
        }

        $this->info("Testing with plot: {$plot->name} (ID: {$plot->id})");
        $this->newLine();

        // Test the service
        try {
            $service = new NasaEarthdataService();
            
            $this->info('📡 Fetching NDVI data...');
            $data = $service->getLatestData($plot, true);

            if ($data) {
                $this->info('✅ Data retrieved successfully!');
                $this->newLine();
                
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['NDVI Mean', $data->ndvi_mean],
                        ['NDVI Min', $data->ndvi_min],
                        ['NDVI Max', $data->ndvi_max],
                        ['Health Status', $data->health_status],
                        ['Image Date', $data->image_date->format('Y-m-d')],
                        ['Image Source', $data->image_source],
                        ['Cloud Coverage', $data->cloud_coverage . '%'],
                    ]
                );

                if (str_contains($data->image_source, 'Mock')) {
                    $this->warn('⚠️  This is MOCK data. To test real API, set NASA_EARTHDATA_MOCK=false');
                } else {
                    $this->info('✅ This is REAL data from NASA satellites!');
                }

                return self::SUCCESS;
            } else {
                $this->error('❌ No data retrieved. Check logs for errors.');
                $this->info('Run: tail -f storage/logs/laravel.log | grep NASA');
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
