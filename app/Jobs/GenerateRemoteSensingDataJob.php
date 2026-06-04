<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenerateRemoteSensingDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $plotId;

    public bool $ultra;

    public ?int $plotSigpacId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $plotId, bool $ultra = true, ?int $plotSigpacId = null)
    {
        $this->plotId = $plotId;
        $this->ultra = $ultra;
        $this->plotSigpacId = $plotSigpacId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting remote sensing data generation', [
                'plot_id' => $this->plotId,
                'ultra' => $this->ultra,
                'plot_sigpac_id' => $this->plotSigpacId,
            ]);

            $args = [
                '--plot-id' => $this->plotId,
                '--ultra' => $this->ultra,
            ];
            if ($this->plotSigpacId) {
                $args['--recinto-id'] = $this->plotSigpacId;
            }

            $exitCode = Artisan::call('remote-sensing:update-enriched', $args);

            if ($exitCode === 0) {
                Log::info('Remote sensing data generated successfully', [
                    'plot_id' => $this->plotId,
                ]);
            } else {
                Log::warning('Remote sensing command returned non-zero exit code', [
                    'plot_id' => $this->plotId,
                    'exit_code' => $exitCode,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate remote sensing data', [
                'plot_id' => $this->plotId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateRemoteSensingDataJob failed', [
            'plot_id' => $this->plotId,
            'error' => $exception->getMessage(),
        ]);
    }
}
