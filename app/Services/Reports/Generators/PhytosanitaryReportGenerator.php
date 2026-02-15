<?php

namespace App\Services\Reports\Generators;

use App\Models\AgriculturalActivity;
use App\Models\DigitalSignature;
use App\Models\OfficialReport;
use App\Models\User;
use App\Services\Reports\DigitalSignatureService;
use App\Services\Reports\ReportExporter;
use App\Services\Reports\ReportPdfGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PhytosanitaryReportGenerator
{
    public function __construct(
        private ReportPdfGenerator $pdfGenerator,
        private DigitalSignatureService $signatureService,
        private ReportExporter $exporter
    ) {}

    /**
     * Generar informe oficial de tratamientos fitosanitarios
     */
    public function generate(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        string $password
    ): OfficialReport {
        // 1. Validar contraseña de firma digital
        $user = User::findOrFail($userId);
        $this->validateDigitalSignature($user, $password);

        // 2. Obtener tratamientos
        $treatments = $this->fetchTreatments($userId, $startDate, $endDate);

        if ($treatments->isEmpty()) {
            throw new \Exception('No hay tratamientos fitosanitarios registrados en el periodo seleccionado.');
        }

        // 3. Calcular estadísticas
        $stats = $this->calculateStats($treatments);

        // 4. Crear registro temporal del informe
        $report = $this->createTemporaryReport($userId, $startDate, $endDate, $stats);

        try {
            // 5. Generar PDF
            $pdfPath = $this->pdfGenerator->generatePhytosanitaryPdf($report, $user, $treatments, $stats);

            // 6. Calcular hash del PDF
            $pdfHash = $this->pdfGenerator->calculatePdfHash($pdfPath);

            // 7. Generar firma digital
            $signatureData = $this->signatureService->prepareSignatureData(
                'phytosanitary_treatments',
                $userId,
                ['start' => $startDate, 'end' => $endDate],
                $treatments->pluck('id')->toArray(),
                $stats,
                $pdfHash
            );

            $signatureResult = $this->signatureService->generateSignature($signatureData);

            // 8. Actualizar informe con información completa
            $this->finalizeReport($report, $pdfPath, $pdfHash, $signatureResult, $user);

            // 9. Generar exportaciones CSV y XML
            $this->exporter->exportReport($report, $user, $treatments, $stats, 'phytosanitary');

            return $report;
        } catch (\Exception $e) {
            Log::error('Error generando informe fitosanitario', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validar contraseña de firma digital
     */
    protected function validateDigitalSignature(User $user, string $password): void
    {
        $digitalSignature = DigitalSignature::forUser($user->id);

        if (!$digitalSignature) {
            throw new \Exception('No tienes una contraseña de firma digital configurada.');
        }

        if (!$digitalSignature->verifyPassword($password)) {
            throw new \Exception('Contraseña de firma digital incorrecta.');
        }
    }

    /**
     * Obtener tratamientos fitosanitarios
     */
    protected function fetchTreatments(int $userId, Carbon $startDate, Carbon $endDate)
    {
        // Aumentar límite de memoria temporalmente
        $originalMemoryLimit = ini_get('memory_limit');
        $memoryLimit = config('reports.storage.memory_limit', '512M');
        ini_set('memory_limit', $memoryLimit);

        try {
            return AgriculturalActivity::ofType('phytosanitary')
                ->forUser($userId)
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->with([
                    'phytosanitaryTreatment.product:id,name,registration_number,withdrawal_period_days',
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
        } finally {
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }

    /**
     * Calcular estadísticas del informe
     */
    protected function calculateStats($treatments): array
    {
        return [
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
    }

    /**
     * Crear registro temporal del informe
     */
    protected function createTemporaryReport(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        array $stats
    ): OfficialReport {
        return OfficialReport::create([
            'user_id' => $userId,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => $startDate,
            'period_end' => $endDate,
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
        User $user
    ): void {
        $report->update([
            'signature_hash' => $signatureResult['hash'],
            'signature_metadata' => $this->signatureService->prepareSignatureMetadata(
                $user,
                $pdfHash,
                $signatureResult
            ),
            'pdf_path' => $pdfPath,
            'pdf_size' => Storage::disk('local')->size($pdfPath),
            'pdf_filename' => basename($pdfPath),
        ]);
    }
}
