<?php

namespace App\Services;

use App\Models\OfficialReport;
use App\Models\User;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\DigitalSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OfficialReportService
{
    /**
     * Generar informe oficial de tratamientos fitosanitarios
     * 
     * @param int $userId ID del usuario (viticulturist)
     * @param Carbon $startDate Fecha inicio del periodo
     * @param Carbon $endDate Fecha fin del periodo
     * @param string $password Contraseña del usuario para firmar
     * @return OfficialReport
     * @throws \Exception
     */
    public function generatePhytosanitaryReport(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        string $password
    ): OfficialReport {
        // 1. Validar contraseña de firma digital
        $user = User::findOrFail($userId);
        $digitalSignature = DigitalSignature::forUser($userId);
        
        if (!$digitalSignature) {
            throw new \Exception('No tienes una contraseña de firma digital configurada. Por favor, créala en Configuración → Firma Digital.');
        }
        
        if (!$digitalSignature->verifyPassword($password)) {
            throw new \Exception('Contraseña de firma digital incorrecta. No se puede firmar el informe.');
        }

        // Aumentar límite de memoria temporalmente para PDFs grandes
        $originalMemoryLimit = ini_get('memory_limit');
        $memoryLimit = config('reports.storage.memory_limit', '512M');
        ini_set('memory_limit', $memoryLimit);
        
        try {
            $treatments = AgriculturalActivity::ofType('phytosanitary')
                ->forUser($userId)
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->with([
                    'phytosanitaryTreatment.product:id,name,registration_number,withdrawal_period_days',
                    'plot:id,name',
                    // ELIMINADO: plot.sigpacCodes (consume mucha memoria)
                    'plotPlanting:id,plot_id,name',
                    'plotPlanting.grapeVariety:id,name',
                    'crewMember:id,name',
                    // ELIMINADO: crew, machinery (no esenciales)
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
        } finally {
            // Restaurar límite original
            ini_set('memory_limit', $originalMemoryLimit);
        }

        if ($treatments->isEmpty()) {
            throw new \Exception('No hay tratamientos fitosanitarios registrados en el periodo seleccionado.');
        }

        // 3. Calcular estadísticas del informe
        $stats = [
            'total_treatments' => $treatments->count(),
            'total_area_treated' => $treatments->sum(function ($t) {
                return $t->phytosanitaryTreatment->area_treated ?? 0;
            }),
            'products_used' => $treatments
                ->pluck('phytosanitaryTreatment.product.name')
                ->unique()
                ->filter()
                ->values()
                ->toArray(),
            'plots_affected' => $treatments->pluck('plot.name')->unique()->count(),
        ];

        // 4. Generar código de verificación
        $verificationCode = OfficialReport::generateVerificationCode();

        // 5. Generar hash temporal único
        $temporaryHash = OfficialReport::generateTemporaryHash();

        // 6. Crear registro temporal del informe (sin hash aún)
        $report = OfficialReport::create([
            'user_id' => $userId,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => $startDate,
            'period_end' => $endDate,
            'verification_code' => $verificationCode,
            'report_metadata' => $stats,
            // Campos temporales que se actualizarán después
            'signature_hash' => $temporaryHash,
            'signed_at' => now(),
            'signed_ip' => request()->ip(),
        ]);

        // 7. Generar PDF primero
        try {
            $pdfPath = $this->generatePDF($report, $user, $treatments, $stats);
        } catch (\Exception $pdfError) {
            Log::error('Error al generar PDF del informe', [
                'report_id' => $report->id,
                'user_id' => $userId,
                'error' => $pdfError->getMessage(),
            ]);
            throw new \Exception('Error al generar el PDF del informe: ' . $pdfError->getMessage());
        }

        // 8. Calcular hash del PDF
        try {
            $pdfContent = Storage::disk('local')->get($pdfPath);
            if (!$pdfContent) {
                throw new \Exception('No se pudo leer el contenido del PDF generado.');
            }
            $pdfHash = hash('sha256', $pdfContent);
        } catch (\Exception $hashError) {
            Log::error('Error al calcular hash del PDF', [
                'report_id' => $report->id,
                'pdf_path' => $pdfPath,
                'error' => $hashError->getMessage(),
            ]);
            throw new \Exception('Error al procesar el PDF: ' . $hashError->getMessage());
        }

        // 9. Preparar datos para la firma digital (incluyendo hash del PDF)
        $signatureData = [
            'type' => 'phytosanitary_treatments',
            'user_id' => $userId,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'treatment_ids' => $treatments->pluck('id')->toArray(),
            'stats' => $stats,
            'pdf_hash' => $pdfHash, // Hash del PDF incluido en la firma
            'timestamp' => now()->toIso8601String(),
        ];

        // 10. Generar hash de firma (incluye nonce y versión automáticamente)
        $signatureResult = OfficialReport::generateSignatureHash($signatureData);
        $signatureHash = $signatureResult['hash'];

        // 11. Actualizar registro con información completa
        $report->update([
            'signature_hash' => $signatureHash,
            'signature_metadata' => [
                'user_agent' => request()->userAgent(),
                'password_verified' => true,
                'signed_by_name' => $user->name,
                'signed_by_email' => $user->email,
                'device_info' => $this->getDeviceInfo(),
                'timestamp_authority' => 'Agro365 Internal TSA',
                'timestamp_format' => 'ISO8601',
                'timezone' => config('app.timezone'),
                'signature_algorithm' => 'SHA-256',
                'signature_version' => $signatureResult['version'],
                'nonce' => $signatureResult['nonce'],
                'pdf_hash' => $pdfHash,
            ],
            'pdf_path' => $pdfPath,
            'pdf_size' => Storage::disk('local')->size($pdfPath),
            'pdf_filename' => basename($pdfPath),
        ]);

        return $report;
    }

    /**
     * Generar informe de cuaderno digital completo
     * 
     * @param int $userId
     * @param int $campaignId
     * @param string $password
     * @return OfficialReport
     */
    public function generateFullNotebookReport(
        int $userId,
        int $campaignId,
        string $password
    ): OfficialReport {
        // 1. Validar contraseña de firma digital
        $user = User::findOrFail($userId);
        $digitalSignature = DigitalSignature::forUser($userId);
        
        if (!$digitalSignature) {
            throw new \Exception('No tienes una contraseña de firma digital configurada. Por favor, créala en Configuración → Firma Digital.');
        }
        
        if (!$digitalSignature->verifyPassword($password)) {
            throw new \Exception('Contraseña de firma digital incorrecta. No se puede firmar el informe.');
        }

        // 2. Obtener campaña
        $campaign = Campaign::findOrFail($campaignId);

        // 3. Obtener TODAS las actividades de la campaña
        $activities = AgriculturalActivity::forUser($userId)
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

        // 4. Estadísticas
        $stats = [
            'total_activities' => $activities->count(),
            'phytosanitary_count' => $activities->where('activity_type', 'phytosanitary')->count(),
            'fertilization_count' => $activities->where('activity_type', 'fertilization')->count(),
            'irrigation_count' => $activities->where('activity_type', 'irrigation')->count(),
            'cultural_count' => $activities->where('activity_type', 'cultural')->count(),
            'observation_count' => $activities->where('activity_type', 'observation')->count(),
            'harvest_count' => $activities->where('activity_type', 'harvest')->count(),
        ];

        // 5. Generar código de verificación
        $verificationCode = OfficialReport::generateVerificationCode();

        // 6. Generar hash temporal único
        $temporaryHash = OfficialReport::generateTemporaryHash();

        // 7. Crear registro temporal del informe (sin hash aún)
        $report = OfficialReport::create([
            'user_id' => $userId,
            'report_type' => 'full_digital_notebook',
            'period_start' => $campaign->start_date,
            'period_end' => $campaign->end_date,
            'verification_code' => $verificationCode,
            'report_metadata' => array_merge($stats, [
                'campaign_id' => $campaignId,
                'campaign_name' => $campaign->name,
            ]),
            // Campos temporales que se actualizarán después
            'signature_hash' => $temporaryHash,
            'signed_at' => now(),
            'signed_ip' => request()->ip(),
        ]);

        // 8. Generar PDF primero
        Log::info('Iniciando generación de PDF del cuaderno completo', [
            'report_id' => $report->id,
            'user_id' => $userId,
            'campaign_id' => $campaignId,
            'activities_count' => $activities->count(),
        ]);
        
        try {
            $pdfPath = $this->generateFullNotebookPDF($report, $user, $campaign, $activities, $stats);
        } catch (\Exception $pdfError) {
            Log::error('Error al generar PDF del cuaderno completo', [
                'report_id' => $report->id,
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'error' => $pdfError->getMessage(),
            ]);
            throw new \Exception('Error al generar el PDF del informe: ' . $pdfError->getMessage());
        }

        // 9. Calcular hash del PDF
        try {
            $pdfContent = Storage::disk('local')->get($pdfPath);
            if (!$pdfContent) {
                throw new \Exception('No se pudo leer el contenido del PDF generado.');
            }
            $pdfHash = hash('sha256', $pdfContent);
        } catch (\Exception $hashError) {
            Log::error('Error al calcular hash del PDF del cuaderno completo', [
                'report_id' => $report->id,
                'pdf_path' => $pdfPath,
                'error' => $hashError->getMessage(),
            ]);
            throw new \Exception('Error al procesar el PDF: ' . $hashError->getMessage());
        }

        // 10. Preparar datos para la firma digital (incluyendo hash del PDF)
        $signatureData = [
            'type' => 'full_digital_notebook',
            'user_id' => $userId,
            'campaign_id' => $campaignId,
            'campaign_name' => $campaign->name,
            'period_start' => $campaign->start_date->toDateString(),
            'period_end' => $campaign->end_date->toDateString(),
            'activity_ids' => $activities->pluck('id')->toArray(),
            'stats' => $stats,
            'pdf_hash' => $pdfHash, // Hash del PDF incluido en la firma
            'timestamp' => now()->toIso8601String(),
        ];

        // 11. Generar hash de firma (incluye nonce y versión automáticamente)
        $signatureResult = OfficialReport::generateSignatureHash($signatureData);
        $signatureHash = $signatureResult['hash'];

        // 12. Actualizar registro con información completa
        $report->update([
            'signature_hash' => $signatureHash,
            'signature_metadata' => [
                'user_agent' => request()->userAgent(),
                'password_verified' => true,
                'signed_by_name' => $user->name,
                'signed_by_email' => $user->email,
                'campaign_name' => $campaign->name,
                'timestamp_authority' => 'Agro365 Internal TSA',
                'timestamp_format' => 'ISO8601',
                'timezone' => config('app.timezone'),
                'signature_algorithm' => 'SHA-256',
                'signature_version' => $signatureResult['version'],
                'nonce' => $signatureResult['nonce'],
                'pdf_hash' => $pdfHash,
            ],
            'pdf_path' => $pdfPath,
            'pdf_size' => Storage::disk('local')->size($pdfPath),
            'pdf_filename' => basename($pdfPath),
        ]);

        return $report;
    }

    /**
     * Generar el PDF del informe de tratamientos fitosanitarios
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param \Illuminate\Support\Collection $treatments
     * @param array $stats
     * @return string Path del PDF generado
     */
    public function generatePDF(
        OfficialReport $report,
        User $user,
        $treatments,
        array $stats
    ): string {
        // Cargar profile con provincia
        $profile = $user->profile;
        if ($profile) {
            $profile->load('province');
        }
        
        // Preparar datos para la vista
        $data = [
            'report' => $report,
            'user' => $user,
            'profile' => $profile,
            'treatments' => $treatments,
            'stats' => $stats,
            'period_start' => $report->period_start,
            'period_end' => $report->period_end,
            'verification_code' => $report->verification_code,
            'signature_hash' => $report->short_hash,
            'generated_at' => $report->signed_at,
            'qr_code_url' => $report->verification_url,
        ];

        // Generar QR Code como URL
        $data['qr_code_url'] = $this->generateQRCodeSVG($report->verification_url);

        // Generar PDF usando DomPDF
        try {
            $pdf = Pdf::loadView('reports.phytosanitary-official', $data);
            $pdf->setPaper('A4', 'portrait');
        } catch (\Exception $viewError) {
            Log::error('Error al cargar la vista del PDF', [
                'report_id' => $report->id,
                'error' => $viewError->getMessage(),
                'trace' => $viewError->getTraceAsString(),
            ]);
            throw new \Exception('Error al generar la vista del PDF: ' . $viewError->getMessage());
        }

        // Definir ruta y nombre del archivo
        $filename = sprintf(
            'informe_tratamientos_%s_%s_%s.pdf',
            $user->id,
            $report->period_start->format('Ymd'),
            $report->period_end->format('Ymd')
        );

        // Guardar PDF usando Storage facade
        $path = 'official_reports/' . $filename;
        try {
            $pdfOutput = $pdf->output();
            if (!$pdfOutput) {
                throw new \Exception('El PDF generado está vacío.');
            }
            Storage::disk('local')->put($path, $pdfOutput);
            
            // Verificar que el archivo se guardó correctamente
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('No se pudo guardar el archivo PDF en el almacenamiento.');
            }
        } catch (\Exception $storageError) {
            Log::error('Error al guardar el PDF', [
                'report_id' => $report->id,
                'path' => $path,
                'error' => $storageError->getMessage(),
            ]);
            throw new \Exception('Error al guardar el PDF: ' . $storageError->getMessage());
        }

        return $path;
    }

    /**
     * Generar el PDF del cuaderno digital completo
     * 
     * @param OfficialReport $report
     * @param User $user
     * @param Campaign $campaign
     * @param \Illuminate\Support\Collection $activities
     * @param array $stats
     * @return string Path del PDF generado
     */
    public function generateFullNotebookPDF(
        OfficialReport $report,
        User $user,
        Campaign $campaign,
        $activities,
        array $stats
    ): string {
        // Preparar datos para la vista
        $data = [
            'report' => $report,
            'user' => $user,
            'profile' => $user->profile,
            'campaign' => $campaign,
            'activities' => $activities,
            'stats' => $stats,
            'period_start' => $report->period_start,
            'period_end' => $report->period_end,
            'verification_code' => $report->verification_code,
            'signature_hash' => $report->short_hash,
            'generated_at' => $report->signed_at,
            'qr_code_url' => $report->verification_url,
        ];

        // Generar QR Code como URL
        $data['qr_code_url'] = $this->generateQRCodeSVG($report->verification_url);

        // Generar PDF usando DomPDF
        try {
            $pdf = Pdf::loadView('reports.full-notebook-official', $data);
            $pdf->setPaper('A4', 'portrait');
        } catch (\Exception $viewError) {
            Log::error('Error al cargar la vista del PDF del cuaderno completo', [
                'report_id' => $report->id,
                'error' => $viewError->getMessage(),
                'trace' => $viewError->getTraceAsString(),
            ]);
            throw new \Exception('Error al generar la vista del PDF: ' . $viewError->getMessage());
        }

        // Definir ruta y nombre del archivo
        $filename = sprintf(
            'cuaderno_completo_%s_%s_%s.pdf',
            $user->id,
            $campaign->id,
            $report->period_start->format('Ymd')
        );

        // Guardar PDF usando Storage facade
        $path = 'official_reports/' . $filename;
        try {
            $pdfOutput = $pdf->output();
            if (!$pdfOutput) {
                throw new \Exception('El PDF generado está vacío.');
            }
            Storage::disk('local')->put($path, $pdfOutput);
            
            // Verificar que el archivo se guardó correctamente
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('No se pudo guardar el archivo PDF en el almacenamiento.');
            }
            
        } catch (\Exception $storageError) {
            Log::error('Error al guardar el PDF del cuaderno completo', [
                'report_id' => $report->id,
                'path' => $path,
                'error' => $storageError->getMessage(),
            ]);
            throw new \Exception('Error al guardar el PDF: ' . $storageError->getMessage());
        }

        return $path;
    }

    /**
     * Generar código QR como imagen (mejorado para PDFs)
     * Usa API pública con mejor calidad y fallback
     * 
     * @param string $url
     * @return string URL del QR code o data URI
     */
    protected function generateQRCodeSVG(string $url): string
    {
        try {
            // Intentar usar API de QR Server con mejor calidad
            $size = 300; // Mayor tamaño para mejor calidad en PDF
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/'
                . '?size=' . $size . 'x' . $size
                . '&data=' . urlencode($url)
                . '&ecc=H' // Error correction level High para mejor legibilidad
                . '&margin=2'
                . '&format=png';
            
            return $qrCodeUrl;
        } catch (\Exception $e) {
            Log::error('Error generando QR code, usando fallback: ' . $e->getMessage());
            // Fallback a Google Charts
            $size = 200;
            return 'https://chart.googleapis.com/chart?'
                . 'chs=' . $size . 'x' . $size
                . '&cht=qr'
                . '&chl=' . urlencode($url)
                . '&choe=UTF-8';
        }
    }

    /**
     * Descargar PDF del informe
     * 
     * @param OfficialReport $report
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadReport(OfficialReport $report)
    {
        if (!$report->pdfExists()) {
            throw new \Exception('El archivo PDF no existe o no se puede encontrar.');
        }

        // Si el path es relativo (usando Storage), obtener la ruta completa
        if (!str_starts_with($report->pdf_path, storage_path())) {
            $fullPath = Storage::disk('local')->path($report->pdf_path);
        } else {
            $fullPath = $report->pdf_path;
        }

        return response()->download(
            $fullPath,
            $report->pdf_filename ?? 'informe_oficial.pdf'
        );
    }

    /**
     * Eliminar PDF del informe
     * 
     * @param OfficialReport $report
     */
    public function deletePDF(OfficialReport $report): void
    {
        if ($report->pdfExists()) {
            // Log antes de eliminar
            Log::warning('PDF de informe eliminado', [
                'report_id' => $report->id,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);

            // Eliminar usando Storage
            if (!str_starts_with($report->pdf_path, storage_path())) {
                Storage::disk('local')->delete($report->pdf_path);
            } else {
                if (file_exists($report->pdf_path)) {
                    unlink($report->pdf_path);
                }
            }
            
            $report->update([
                'pdf_path' => null,
                'pdf_size' => null,
                'pdf_filename' => null,
            ]);
        }
    }

    /**
     * Obtener información del dispositivo
     * 
     * @return array
     */
    protected function getDeviceInfo(): array
    {
        $userAgent = request()->userAgent();
        
        // Detectar navegador
        $browser = 'Unknown';
        if (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
        elseif (str_contains($userAgent, 'Edge')) $browser = 'Edge';
        
        // Detectar SO
        $os = 'Unknown';
        if (str_contains($userAgent, 'Windows')) $os = 'Windows';
        elseif (str_contains($userAgent, 'Mac')) $os = 'macOS';
        elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';
        elseif (str_contains($userAgent, 'Android')) $os = 'Android';
        elseif (str_contains($userAgent, 'iOS')) $os = 'iOS';
        
        return [
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $userAgent,
        ];
    }
}
