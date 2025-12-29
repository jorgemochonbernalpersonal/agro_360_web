<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Notifications\RemoteSensingAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRemoteSensingAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check remote sensing data for all plots and send alerts if thresholds are breached';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting remote sensing alert check...');
        
        $plots = Plot::where('active', true)
            ->whereNotNull('ndvi_alert_threshold')
            ->with('viticulturist')
            ->get();
            
        $service = new NasaEarthdataService();
        $alertsSent = 0;
        
        foreach ($plots as $plot) {
            try {
                // Get latest data (force fresh check if needed, but usually cache is fine)
                $data = $service->getLatestData($plot);
                
                if ($data && $data->ndvi_mean < $plot->ndvi_alert_threshold) {
                    
                    // Logic to avoid spamming alerts every minute
                    // Check if we sent an alert today directly via notification history?
                    // For simplicity in this iteration, we trust the scheduler runs once daily.
                    // Or we could check the last notification date, but let's keep it simple first.
                    
                    if ($plot->viticulturist) {
                        $plot->viticulturist->notify(new RemoteSensingAlertNotification(
                            $plot, 
                            $data->ndvi_mean, 
                            $plot->ndvi_alert_threshold
                        ));
                        
                        $this->info("Alert sent for plot: {$plot->name} (NDVI: {$data->ndvi_mean} < {$plot->ndvi_alert_threshold})");
                        $alertsSent++;
                    }
                }
                
            } catch (\Exception $e) {
                Log::error("Error checking alerts for plot {$plot->id}: " . $e->getMessage());
                $this->error("Error checking plot {$plot->id}");
            }
        }
        
        $this->info("Done. Sent {$alertsSent} alerts.");
    }
}
