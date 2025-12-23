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
    public $timeout = 600; // 10 minutos (aumentado para informes grandes)
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
        // Optimización: Aumentar límites de memoria y tiempo para trabajos largos
        ini_set('memory_limit', '512M');
        set_time_limit(600); // 10 minutos
        
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
                'type' => $this->reportType,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);

            $service = new OfficialReportService();
            
            // Generar datos según tipo
            if ($this->reportType === 'phytosanitary_treatments') {
                // OPTIMIZACIÓN: Cargar solo campos necesarios y optimizar relaciones
                $treatments = \App\Models\AgriculturalActivity::ofType('phytosanitary')
                    ->forUser($this->userId)
                    ->whereBetween('activity_date', [
                        Carbon::parse($this->parameters['start_date']),
                        Carbon::parse($this->parameters['end_date'])
                    ])
                    ->with([
                        'phytosanitaryTreatment:id,activity_id,product_id,area_treated',
                        'phytosanitaryTreatment.product:id,name',
                        'plot:id,name,area',
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
                
                Log::info('Treatments loaded', [
                    'count' => $treatments->count(),
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                ]);

                // OPTIMIZACIÓN: Calcular estadísticas de forma más eficiente
                $stats = [
                    'total_treatments' => $treatments->count(),
                    'total_area_treated' => $treatments->sum(fn($t) => $t->phytosanitaryTreatment?->area_treated ?? 0),
                    'products_used' => $treatments->pluck('phytosanitaryTreatment.product.name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray(),
                    'plots_affected' => $treatments->pluck('plot.name')
                        ->filter()
                        ->unique()
                        ->count(),
                ];

                // Actualizar metadata
                $report->update(['report_metadata' => $stats]);

                // Generar PDF
                $user = \App\Models\User::find($this->userId);
                $pdfPath = $service->generatePDF($report, $user, $treatments, $stats);

                // OPTIMIZACIÓN: Calcular hash sin cargar todo en memoria
                $pdfHash = $this->calculateFileHash($pdfPath);
                $pdfSize = \Illuminate\Support\Facades\Storage::disk('local')->size($pdfPath);

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
                    'pdf_size' => $pdfSize,
                    'is_valid' => true,
                    'processing_status' => 'completed',
                    'completed_at' => now(),
                ]);
            } else {
                // Cuaderno digital completo
                $campaignId = $this->parameters['campaign_id'];
                $campaign = \App\Models\Campaign::findOrFail($campaignId);

                // OPTIMIZACIÓN: Contar primero para decidir si usar chunking
                $totalActivities = \App\Models\AgriculturalActivity::forUser($this->userId)
                    ->forCampaign($campaignId)
                    ->count();

                Log::info('Loading activities for full notebook', [
                    'campaign_id' => $campaignId,
                    'total_activities' => $totalActivities,
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                ]);

                // Para grandes volúmenes (>1000), usar chunking
                if ($totalActivities > 1000) {
                    $activities = $this->loadActivitiesInChunks($this->userId, $campaignId);
                    Log::info('Activities loaded in chunks', [
                        'count' => $activities->count(),
                        'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    ]);
                } else {
                    // Para volúmenes normales, cargar optimizado
                    $activities = \App\Models\AgriculturalActivity::forUser($this->userId)
                        ->forCampaign($campaignId)
                        ->with([
                            'plot:id,name',
                            'plotPlanting:id,plot_id,name',
                            'plotPlanting.grapeVariety:id,name',
                            'phytosanitaryTreatment:id,activity_id,product_id',
                            'phytosanitaryTreatment.product:id,name',
                            'fertilization:id,activity_id',
                            'irrigation:id,activity_id',
                            'culturalWork:id,activity_id',
                            'observation:id,activity_id',
                            'harvest:id,activity_id',
                            'crew:id,name',
                            'crewMember:id,name',
                            'machinery:id,name',
                        ])
                        ->orderBy('activity_date', 'asc')
                        ->get();
                    
                    Log::info('Activities loaded normally', [
                        'count' => $activities->count(),
                        'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    ]);
                }

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

                // OPTIMIZACIÓN: Calcular hash sin cargar todo en memoria
                $pdfHash = $this->calculateFileHash($pdfPath);
                $pdfSize = \Illuminate\Support\Facades\Storage::disk('local')->size($pdfPath);

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
                    'pdf_size' => $pdfSize,
                    'is_valid' => true,
                    'processing_status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            Log::info('Report generation completed', [
                'report_id' => $this->reportId,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);

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
            'error' => $exception->getMessage(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);
    }

    /**
     * Calcular hash de archivo sin cargar todo en memoria
     * Optimización para archivos grandes (PDFs)
     */
    private function calculateFileHash(string $filePath): string
    {
        $fullPath = storage_path('app/' . $filePath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Archivo no encontrado: {$filePath}");
        }

        $hash = hash_init('sha256');
        $stream = fopen($fullPath, 'rb');
        
        if (!$stream) {
            throw new \Exception('No se pudo abrir el archivo para calcular hash');
        }
        
        // Leer en chunks de 8KB para no cargar todo en memoria
        while (!feof($stream)) {
            $chunk = fread($stream, 8192);
            if ($chunk !== false) {
                hash_update($hash, $chunk);
            }
        }
        
        fclose($stream);
        return hash_final($hash);
    }

    /**
     * Cargar actividades en chunks para grandes volúmenes
     * Evita problemas de memoria con campañas muy grandes
     */
    private function loadActivitiesInChunks(int $userId, int $campaignId): \Illuminate\Support\Collection
    {
        $allActivities = collect();
        
        \App\Models\AgriculturalActivity::forUser($userId)
            ->forCampaign($campaignId)
            ->with([
                'plot:id,name',
                'plotPlanting:id,plot_id,name',
                'plotPlanting.grapeVariety:id,name',
                'phytosanitaryTreatment:id,activity_id,product_id',
                'phytosanitaryTreatment.product:id,name',
                'fertilization:id,activity_id',
                'irrigation:id,activity_id',
                'culturalWork:id,activity_id',
                'observation:id,activity_id',
                'harvest:id,activity_id',
                'crew:id,name',
                'crewMember:id,name',
                'machinery:id,name',
            ])
            ->orderBy('activity_date', 'asc')
            ->chunk(500, function ($chunk) use (&$allActivities) {
                $allActivities = $allActivities->merge($chunk);
                
                // Log de progreso cada 500 registros
                Log::debug('Loading activities chunk', [
                    'loaded' => $allActivities->count(),
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                ]);
            });
        
        return $allActivities;
    }
}
