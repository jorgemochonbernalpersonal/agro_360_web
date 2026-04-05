<?php

namespace App\Jobs;

use App\DataTransferObjects\ReportGenerationData;
use App\Events\OfficialReportGenerated;
use App\Models\OfficialReport;
use App\Models\User;
use App\Notifications\ReportFailedNotification;
use App\Services\OfficialReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ReportGenerationData $reportData,
        public User $user,
        public string $password
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OfficialReportService $reportService): void
    {
        Log::info('Starting report generation job', [
            'user_id' => $this->user->id,
            'report_type' => $this->reportData->type,
        ]);

        try {
            $report = match ($this->reportData->type) {
                'phytosanitary' => $reportService->generatePhytosanitaryReport(
                    $this->user,
                    $this->reportData->periodStart,
                    $this->reportData->periodEnd,
                    $this->password
                ),
                'full_notebook' => $reportService->generateFullNotebookReport(
                    $this->user,
                    $this->reportData->campaignId,
                    $this->password
                ),
                default => throw new \InvalidArgumentException("Invalid report type: {$this->reportData->type}")
            };

            // Disparar evento de reporte generado
            event(new OfficialReportGenerated($report));

            Log::info('Report generated successfully', [
                'report_id' => $report->id,
                'verification_code' => $report->verification_code,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'user_id' => $this->user->id,
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
        Log::error('Report generation job failed permanently', [
            'user_id' => $this->user->id,
            'report_type' => $this->reportData->type,
            'error' => $exception->getMessage(),
        ]);

        $report = OfficialReport::where('user_id', $this->user->id)
            ->where('report_type', $this->reportData->type)
            ->latest()
            ->first();

        if ($report) {
            $this->user->notify(new ReportFailedNotification($report, $exception->getMessage()));
        }
    }
}
