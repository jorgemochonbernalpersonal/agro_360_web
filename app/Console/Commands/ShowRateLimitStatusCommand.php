<?php

namespace App\Console\Commands;

use App\Services\RemoteSensing\RateLimitService;
use Illuminate\Console\Command;

/**
 * Show current rate limit status for remote sensing APIs
 */
class ShowRateLimitStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'remote-sensing:rate-limit-status
                            {--reset= : Reset counters for a service (nasa|open_meteo)}';

    /**
     * The console command description.
     */
    protected $description = 'Show rate limit status for remote sensing APIs';

    /**
     * Execute the console command.
     */
    public function handle(RateLimitService $rateLimitService): int
    {
        // Handle reset if requested
        if ($service = $this->option('reset')) {
            if (!in_array($service, ['nasa', 'open_meteo'])) {
                $this->error("Invalid service: {$service}. Use 'nasa' or 'open_meteo'");
                return self::FAILURE;
            }

            if ($this->confirm("Are you sure you want to reset rate limit counters for {$service}?")) {
                $rateLimitService->resetCounters($service);
                $this->info("✅ Counters reset for {$service}");
            }
            
            return self::SUCCESS;
        }

        // Show status
        $this->info('📊 Remote Sensing API Rate Limits');
        $this->newLine();

        $allUsage = $rateLimitService->getAllUsage();

        foreach ($allUsage as $serviceName => $usage) {
            $this->displayServiceStatus($serviceName, $usage);
            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * Display status for a service
     */
    private function displayServiceStatus(string $name, array $usage): void
    {
        $this->line("🌐 <fg=cyan>" . strtoupper($name) . "</>");
        $this->newLine();

        // Hourly limits
        $hourColor = $this->getColorForPercentage($usage['hour']['percentage']);
        $this->line("  ⏰ <fg={$hourColor}>Hourly:</>  {$usage['hour']['used']}/{$usage['hour']['limit']} ({$usage['hour']['percentage']}%)");
        $this->line("     Remaining: {$usage['hour']['remaining']} requests");
        
        // Daily limits
        $dayColor = $this->getColorForPercentage($usage['day']['percentage']);
        $this->line("  📅 <fg={$dayColor}>Daily:</>   {$usage['day']['used']}/{$usage['day']['limit']} ({$usage['day']['percentage']}%)");
        $this->line("     Remaining: {$usage['day']['remaining']} requests");

        // Warning if approaching limits
        if ($usage['hour']['percentage'] > 80 || $usage['day']['percentage'] > 80) {
            $this->newLine();
            $this->warn("  ⚠️  Approaching rate limits! Consider delaying some requests.");
        }
    }

    /**
     * Get color based on usage percentage
     */
    private function getColorForPercentage(float $percentage): string
    {
        return match (true) {
            $percentage < 50 => 'green',
            $percentage < 80 => 'yellow',
            default => 'red',
        };
    }
}
