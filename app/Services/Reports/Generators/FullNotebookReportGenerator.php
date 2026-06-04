<?php

namespace App\Services\Reports\Generators;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\DigitalSignature;
use App\Models\OfficialReport;
use App\Models\User;
use App\Services\Reports\DigitalSignatureService;
use App\Services\Reports\ReportExporter;
use App\Services\Reports\ReportPdfGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FullNotebookReportGenerator
{
    public function __construct(
        private ReportPdfGenerator $pdfGenerator,
        private DigitalSignatureService $signatureService,
        private ReportExporter $exporter
    ) {}

    /**
     * Generar informe de cuaderno digital completo
     */
    public function generate(
        int $userId,
        int $campaignId,
        string $password
    ): OfficialReport {
        // 1. Validar contraseña de firma digital
        $user = User::findOrFail($userId);
        $this->validateDigitalSignature($user, $password);

        // 2. Obtener campaña
        $campaign = Campaign::findOrFail($campaignId);

        // 3. Obtener todas las actividades
        $activities = $this->fetchActivities($userId, $campaignId);

        if ($activities->isEmpty()) {
            throw new \Exception(__('No hay actividades registradas en esta campaña.'));
        }

        // 4. Calcular estadísticas
        $stats = $this->calculateStats($activities, $campaign);

        // 5. Crear registro temporal del informe
        $report = $this->createTemporaryReport($userId, $campaign, $stats);

        try {
            Log::info('Iniciando generación de PDF del cuaderno completo', [
                'report_id' => $report->id,
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'activities_count' => $activities->count(),
            ]);

            // 6. Generar PDF
            $pdfPath = $this->pdfGenerator->generateFullNotebookPdf(
                $report,
                $user,
                $campaign,
                $activities,
                $stats
            );

            // 7. Calcular hash del PDF
            $pdfHash = $this->pdfGenerator->calculatePdfHash($pdfPath);

            // 8. Generar firma digital
            $signatureData = $this->signatureService->prepareSignatureData(
                'full_digital_notebook',
                $userId,
                ['start' => $campaign->start_date, 'end' => $campaign->end_date],
                $activities->pluck('id')->toArray(),
                $stats,
                $pdfHash
            );

            $signatureResult = $this->signatureService->generateSignature($signatureData);

            // 9. Actualizar informe con información completa
            $this->finalizeReport($report, $pdfPath, $pdfHash, $signatureResult, $user, $campaign);

            // 10. Generar exportaciones CSV y XML
            $this->exporter->exportReport($report, $user, $activities, $stats, 'full_notebook');

            return $report;
        } catch (\Exception $e) {
            Log::error('Error generando informe de cuaderno completo', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar solo el PDF (para uso en Jobs)
     *
     * @param mixed $user
     * @param mixed $campaign
     * @param mixed $activities
     * @param mixed $stats
     */
    public function generatePDF(OfficialReport $report, $user, $campaign, $activities, $stats): string
    {
        return $this->pdfGenerator->generateFullNotebookPdf($report, $user, $campaign, $activities, $stats);
    }

    /**
     * Validar contraseña de firma digital
     */
    protected function validateDigitalSignature(User $user, string $password): void
    {
        $digitalSignature = DigitalSignature::forUser($user->id);

        if (! $digitalSignature) {
            throw new \Exception(__('No tienes una contraseña de firma digital configurada.'));
        }

        if (! $digitalSignature->verifyPassword($password)) {
            throw new \Exception(__('Contraseña de firma digital incorrecta.'));
        }
    }

    /**
     * Obtener actividades de la campaña
     */
    protected function fetchActivities(int $userId, int $campaignId)
    {
        return AgriculturalActivity::forUser($userId)
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
    }

    /**
     * Calcular estadísticas del informe
     *
     * @param mixed $activities
     */
    protected function calculateStats($activities, Campaign $campaign): array
    {
        return [
            'total_activities' => $activities->count(),
            'phytosanitary_count' => $activities->where('activity_type', 'phytosanitary')->count(),
            'fertilization_count' => $activities->where('activity_type', 'fertilization')->count(),
            'irrigation_count' => $activities->where('activity_type', 'irrigation')->count(),
            'cultural_count' => $activities->where('activity_type', 'cultural')->count(),
            'observation_count' => $activities->where('activity_type', 'observation')->count(),
            'harvest_count' => $activities->where('activity_type', 'harvest')->count(),
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
        ];
    }

    /**
     * Crear registro temporal del informe
     */
    protected function createTemporaryReport(
        int $userId,
        Campaign $campaign,
        array $stats
    ): OfficialReport {
        return OfficialReport::create([
            'user_id' => $userId,
            'report_type' => 'full_digital_notebook',
            'period_start' => $campaign->start_date,
            'period_end' => $campaign->end_date,
            'verification_code' => OfficialReport::generateVerificationCode(),
            'report_metadata' => $stats,
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
            'signed_ip' => request()->ip(),
        ]);
    }

    /**
     * Finalizar el informe con toda la información
     */
    protected function finalizeReport(
        OfficialReport $report,
        string $pdfPath,
        string $pdfHash,
        array $signatureResult,
        User $user,
        Campaign $campaign
    ): void {
        $signatureMetadata = $this->signatureService->prepareSignatureMetadata(
            $user,
            $pdfHash,
            $signatureResult
        );

        // Agregar nombre de campaña al metadata
        $signatureMetadata['campaign_name'] = $campaign->name;

        $report->update([
            'signature_hash' => $signatureResult['hash'],
            'signature_metadata' => $signatureMetadata,
            'pdf_path' => $pdfPath,
            'pdf_size' => Storage::disk('local')->size($pdfPath),
            'pdf_filename' => basename($pdfPath),
        ]);
    }
}
