<?php

namespace App\Jobs;

use App\Models\OfficialReport;
use App\Services\OfficialReportService;
use App\Mail\ReportGeneratedMail;
use App\Mail\ReportGenerationFailedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateOfficialReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutos
    public $backoff = [60, 120, 300]; // Reintentos: 1min, 2min, 5min

    public function __construct(
        public int $reportId,
        public int $userId,
        public string $reportType,
        public array $parameters,
        public string $password
    ) {}

    public function handle()
    {
        $report = OfficialReport::find($this->reportId);
        
        if (!$report) {
            Log::error('Report not found for job', ['id' => $this->reportId]);
            return;
        }

        try {
            // Marcar como procesando
            $report->update(['processing_status' => 'processing']);
            
            Log::info('Starting report generation', [
                'report_id' => $this->reportId,
                'type' => $this->reportType
            ]);

            $service = new OfficialReportService();
            
            // Generar datos según tipo
            if ($this->reportType === 'phytosanitary_treatments') {
                // Obtener tratamientos
                $treatments = \App\Models\AgriculturalActivity::ofType('phytosanitary')
                    ->forUser($this->userId)
                    ->whereBetween('activity_date', [
                        Carbon::parse($this->parameters['start_date']),
                        Carbon::parse($this->parameters['end_date'])
                    ])
                    ->with([
                        'phytosanitaryTreatment.product',
                        'plot:id,name',
                        'plotPlanting:id,plot_id,name',
                        'plotPlanting.grapeVariety:id,name',
                        'crewMember:id,name',
                    ])
                    ->select([
                        'id',
                        'activity_date',
                        'plot_id',
                        'plot_planting_id',
                        'crew_member_id',
                        'temperature',
                        'notes',
                        'viticulturist_id'
                    ])
                    ->orderBy('activity_date', 'asc')
                    ->get();

                // Calcular estadísticas
                $stats = [
                    'total_treatments' => $treatments->count(),
                    'total_area_treated' => $treatments->sum(fn($t) => $t->phytosanitaryTreatment->area_treated ?? 0),
                    'products_used' => $treatments->pluck('phytosanitaryTreatment.product.name')->unique()->filter()->values()->toArray(),
                    'plots_affected' => $treatments->pluck('plot.name')->unique()->count(),
                ];

                // Actualizar metadata
                $report->update(['report_metadata' => $stats]);

                // Generar PDF
                $user = \App\Models\User::find($this->userId);
                $pdfPath = $service->generatePDF($report, $user, $treatments, $stats);

                // Calcular hash del PDF
                $pdfContent = \Illuminate\Support\Facades\Storage::disk('local')->get($pdfPath);
                $pdfHash = hash('sha256', $pdfContent);

                // Generar firma
                $signatureData = [
                    'type' => 'phytosanitary_treatments',
                    'user_id' => $this->userId,
                    'period_start' => $this->parameters['start_date'],
                    'period_end' => $this->parameters['end_date'],
                    'treatment_ids' => $treatments->pluck('id')->toArray(),
                    'stats' => $stats,
                    'pdf_hash' => $pdfHash,
                    'timestamp' => now()->toIso8601String(),
                ];

                $signatureResult = \App\Models\OfficialReport::generateSignatureHash($signatureData);

                // Actualizar report con datos reales
                $report->update([
                    'signature_hash' => $signatureResult['hash'],
                    'signature_metadata' => [
                        'password_verified' => true,
                        'signed_by_name' => $user->name,
                        'signed_by_email' => $user->email,
                        'signature_algorithm' => 'SHA-256',
                        'signature_version' => $signatureResult['version'],
                        'nonce' => $signatureResult['nonce'],
                        'pdf_hash' => $pdfHash,
                    ],
                    'pdf_path' => $pdfPath,
                    'pdf_size' => strlen($pdfContent),
                    'is_valid' => true,
                    'processing_status' => 'completed',  // ✅ CRITICAL FIX: Mark as completed
                    'completed_at' => now(),             // ✅ CRITICAL FIX: Set completion timestamp
                ]);
            } else {
                // Cuaderno digital completo
                $campaignId = $this->parameters['campaign_id'];
                $campaign = \App\Models\Campaign::findOrFail($campaignId);

                // Obtener TODAS las actividades de la campaña
                $activities = \App\Models\AgriculturalActivity::forUser($this->userId)
                    ->forCampaign($campaignId)
                    ->with([
                        'plot',
                        'plotPlanting.grapeVariety',
                        'phytosanitaryTreatment.product',
                        'fertilization',
                        'irrigation',
                        'culturalWork',
                        'observation',
                        'harvest',
                        'crew',
                        'crewMember',
                        'machinery',
                    ])
                    ->orderBy('activity_date', 'asc')
                    ->get();

                if ($activities->isEmpty()) {
                    throw new \Exception('No hay actividades registradas en esta campaña.');
                }

                // Estadísticas
                $stats = [
                    'total_activities' => $activities->count(),
                    'phytosanitary_count' => $activities->where('activity_type', 'phytosanitary')->count(),
                    'fertilization_count' => $activities->where('activity_type', 'fertilization')->count(),
                    'irrigation_count' => $activities->where('activity_type', 'irrigation')->count(),
                    'cultural_count' => $activities->where('activity_type', 'cultural')->count(),
                    'observation_count' => $activities->where('activity_type', 'observation')->count(),
                    'harvest_count' => $activities->where('activity_type', 'harvest')->count(),
                ];

                // Actualizar metadata
                $report->update(['report_metadata' => array_merge($stats, [
                    'campaign_id' => $campaignId,
                    'campaign_name' => $campaign->name,
                ])]);

                // Generar PDF
                $user = \App\Models\User::find($this->userId);
                $service = new OfficialReportService();
                $pdfPath = $service->generateFullNotebookPDF($report, $user, $campaign, $activities, $stats);

                // Calcular hash del PDF
                $pdfContent = \Illuminate\Support\Facades\Storage::disk('local')->get($pdfPath);
                $pdfHash = hash('sha256', $pdfContent);

                // Generar firma
                $signatureData = [
                    'type' => 'full_digital_notebook',
                    'user_id' => $this->userId,
                    'campaign_id' => $campaignId,
                    'campaign_name' => $campaign->name,
                    'period_start' => $campaign->start_date->toDateString(),
                    'period_end' => $campaign->end_date->toDateString(),
                    'activity_ids' => $activities->pluck('id')->toArray(),
                    'stats' => $stats,
                    'pdf_hash' => $pdfHash,
                    'timestamp' => now()->toIso8601String(),
                ];

                $signatureResult = \App\Models\OfficialReport::generateSignatureHash($signatureData);

                // Actualizar report con datos reales
                $report->update([
                    'signature_hash' => $signatureResult['hash'],
                    'signature_metadata' => [
                        'password_verified' => true,
                        'signed_by_name' => $user->name,
                        'signed_by_email' => $user->email,
                        'signature_algorithm' => 'SHA-256',
                        'signature_version' => $signatureResult['version'],
                        'nonce' => $signatureResult['nonce'],
                        'pdf_hash' => $pdfHash,
                    ],
                    'pdf_path' => $pdfPath,
                    'pdf_size' => strlen($pdfContent),
                    'is_valid' => true,
                    'processing_status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            Log::info('Report generation completed', ['report_id' => $this->reportId]);

            // Enviar notificación en la app
            try {
                $report->user->notify(new \App\Notifications\ReportGeneratedNotification($report));
            } catch (\Exception $notifError) {
                Log::warning('Failed to send notification', [
                    'report_id' => $this->reportId,
                    'error' => $notifError->getMessage()
                ]);
            }

            // Enviar email de éxito
            try {
                Mail::to($report->user->email)
                    ->send(new ReportGeneratedMail($report));
            } catch (\Exception $mailError) {
                Log::warning('Failed to send success email', [
                    'report_id' => $this->reportId,
                    'error' => $mailError->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Report generation failed', [
                'report_id' => $this->reportId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Marcar como fallido
            $report->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage()
            ]);

            // Enviar notificación en la app
            try {
                $report->user->notify(new \App\Notifications\ReportFailedNotification($report, $e->getMessage()));
            } catch (\Exception $notifError) {
                Log::warning('Failed to send failure notification', [
                    'report_id' => $this->reportId,
                    'error' => $notifError->getMessage()
                ]);
            }

            // Enviar email de error
            try {
                Mail::to($report->user->email)
                    ->send(new ReportGenerationFailedMail($report, $e->getMessage()));
            } catch (\Exception $mailError) {
                Log::warning('Failed to send error email', [
                    'report_id' => $this->reportId,
                    'error' => $mailError->getMessage()
                ]);
            }

            // Re-lanzar para reintentos automáticos
            throw $e;
        }
    }

    /**
     * Manejar fallo del job (después de todos los reintentos)
     */
    public function failed(\Throwable $exception)
    {
        $report = OfficialReport::find($this->reportId);
        
        if ($report) {
            $report->update([
                'processing_status' => 'failed',
                'processing_error' => 'Error permanente después de ' . $this->tries . ' intentos: ' . $exception->getMessage()
            ]);
        }

        Log::critical('Report job permanently failed', [
            'report_id' => $this->reportId,
            'attempts' => $this->tries,
            'error' => $exception->getMessage()
        ]);
    }
}
