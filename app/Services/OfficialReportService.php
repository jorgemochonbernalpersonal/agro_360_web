<?php

namespace App\Services;

use App\Models\OfficialReport;
use App\Services\Reports\Generators\FullNotebookReportGenerator;
use App\Services\Reports\Generators\PhytosanitaryReportGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio principal de informes oficiales
 * Orquesta los diferentes generadores de informes
 */
class OfficialReportService
{
    public function __construct(
        private PhytosanitaryReportGenerator $phytosanitaryGenerator,
        private FullNotebookReportGenerator $fullNotebookGenerator
    ) {}

    /**
     * Generar informe oficial de tratamientos fitosanitarios
     */
    public function generatePhytosanitaryReport(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        string $password
    ): OfficialReport {
        return $this->phytosanitaryGenerator->generate($userId, $startDate, $endDate, $password);
    }

    /**
     * Generar informe de cuaderno digital completo
     */
    public function generateFullNotebookReport(
        int $userId,
        int $campaignId,
        string $password
    ): OfficialReport {
        return $this->fullNotebookGenerator->generate($userId, $campaignId, $password);
    }

    /**
     * Generar PDF de tratamientos fitosanitarios (llamado por Job)
     */
    public function generatePDF(OfficialReport $report, $user, $treatments, $stats): string
    {
        return $this->phytosanitaryGenerator->generatePDF($report, $user, $treatments, $stats);
    }

    /**
     * Generar PDF de cuaderno digital completo (llamado por Job)
     */
    public function generateFullNotebookPDF(OfficialReport $report, $user, $campaign, $activities, $stats): string
    {
        return $this->fullNotebookGenerator->generatePDF($report, $user, $campaign, $activities, $stats);
    }

    /**
     * Descargar PDF del informe
     */
    public function downloadReport(OfficialReport $report)
    {
        if (!$report->pdfExists()) {
            throw new \Exception('El archivo PDF no existe o no se puede encontrar.');
        }

        $fullPath = $this->getFullPdfPath($report);

        return response()->download(
            $fullPath,
            $report->pdf_filename ?? 'informe_oficial.pdf'
        );
    }

    /**
     * Descargar informe en formato especificado
     */
    public function downloadReportInFormat(OfficialReport $report, string $format)
    {
        // Validar formato
        if (!in_array($format, ['pdf', 'csv', 'xml'])) {
            throw new \Exception('Formato no válido. Usa "pdf", "csv" o "xml".');
        }

        // Obtener el path y filename según el formato
        [$path, $filename] = $this->getReportPathAndFilename($report, $format);

        // Obtener ruta completa
        $fullPath = $this->getFullPath($path);

        // Log de descarga para auditoría
        Log::info('Descarga de informe en formato ' . strtoupper($format), [
            'report_id' => $report->id,
            'format' => $format,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        return response()->download($fullPath, $filename);
    }

    /**
     * Eliminar PDF del informe
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
     * Obtener path y filename según formato
     */
    protected function getReportPathAndFilename(OfficialReport $report, string $format): array
    {
        return match ($format) {
            'pdf' => [
                $report->pdf_path ?? throw new \Exception('El archivo PDF no existe.'),
                $report->pdf_filename ?? 'informe_oficial.pdf'
            ],
            'csv' => [
                $report->csv_path ?? throw new \Exception('El archivo CSV no está disponible.'),
                basename($report->csv_path)
            ],
            'xml' => [
                $report->xml_path ?? throw new \Exception('El archivo XML no está disponible.'),
                basename($report->xml_path)
            ],
        };
    }

    /**
     * Obtener ruta completa del PDF
     */
    protected function getFullPdfPath(OfficialReport $report): string
    {
        return $this->getFullPath($report->pdf_path);
    }

    /**
     * Obtener ruta completa de un path
     */
    protected function getFullPath(string $path): string
    {
        if (!str_starts_with($path, storage_path())) {
            return Storage::disk('local')->path($path);
        }
        return $path;
    }
}
