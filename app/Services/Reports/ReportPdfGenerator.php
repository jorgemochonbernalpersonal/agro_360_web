<?php

namespace App\Services\Reports;

use App\Models\OfficialReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportPdfGenerator
{
    /**
     * Generar PDF de informe de tratamientos fitosanitarios
     */
    public function generatePhytosanitaryPdf(
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
            'qr_code_url' => $this->generateQRCodeUrl($report->verification_url),
        ];

        return $this->generatePdf(
            'reports.phytosanitary-official',
            $data,
            $report,
            sprintf(
                'informe_tratamientos_%s_%s_%s.pdf',
                $user->id,
                $report->period_start->format('Ymd'),
                $report->period_end->format('Ymd')
            )
        );
    }

    /**
     * Generar PDF de cuaderno completo
     */
    public function generateFullNotebookPdf(
        OfficialReport $report,
        User $user,
        $campaign,
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
            'qr_code_url' => $this->generateQRCodeUrl($report->verification_url),
        ];

        return $this->generatePdf(
            'reports.full-notebook-official',
            $data,
            $report,
            sprintf(
                'cuaderno_completo_%s_%s_%s.pdf',
                $user->id,
                $campaign->id,
                $report->period_start->format('Ymd')
            )
        );
    }

    /**
     * Generar PDF usando DomPDF
     */
    protected function generatePdf(
        string $view,
        array $data,
        OfficialReport $report,
        string $filename
    ): string {
        try {
            $pdf = Pdf::loadView($view, $data);
            $pdf->setPaper('A4', 'portrait');
        } catch (\Exception $viewError) {
            Log::error('Error al cargar la vista del PDF', [
                'report_id' => $report->id,
                'view' => $view,
                'error' => $viewError->getMessage(),
            ]);
            throw new \Exception(__('Error al generar la vista del PDF: :error', ['error' => $viewError->getMessage()]));
        }

        // Guardar PDF
        $path = 'official_reports/' . $filename;
        try {
            $pdfOutput = $pdf->output();
            if (!$pdfOutput) {
                throw new \Exception(__('El PDF generado está vacío.'));
            }
            Storage::disk('local')->put($path, $pdfOutput);

            // Verificar que se guardó
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception(__('No se pudo guardar el archivo PDF.'));
            }
        } catch (\Exception $storageError) {
            Log::error('Error al guardar el PDF', [
                'report_id' => $report->id,
                'path' => $path,
                'error' => $storageError->getMessage(),
            ]);
            throw new \Exception(__('Error al guardar el PDF: :error', ['error' => $storageError->getMessage()]));
        }

        return $path;
    }

    /**
     * Calcular hash SHA-256 de un PDF
     */
    public function calculatePdfHash(string $pdfPath): string
    {
        try {
            $pdfContent = Storage::disk('local')->get($pdfPath);
            if (!$pdfContent) {
                throw new \Exception(__('No se pudo leer el contenido del PDF generado.'));
            }
            return hash('sha256', $pdfContent);
        } catch (\Exception $e) {
            Log::error('Error al calcular hash del PDF', [
                'pdf_path' => $pdfPath,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(__('Error al procesar el PDF: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Generar URL de QR code
     */
    protected function generateQRCodeUrl(string $url): string
    {
        try {
            $size = 300;
            return 'https://api.qrserver.com/v1/create-qr-code/'
                . '?size=' . $size . 'x' . $size
                . '&data=' . urlencode($url)
                . '&ecc=H'
                . '&margin=2'
                . '&format=png';
        } catch (\Exception $e) {
            Log::error('Error generando QR code, usando fallback: ' . $e->getMessage());
            $size = 200;
            return 'https://chart.googleapis.com/chart?'
                . 'chs=' . $size . 'x' . $size
                . '&cht=qr'
                . '&chl=' . urlencode($url)
                . '&choe=UTF-8';
        }
    }
}
