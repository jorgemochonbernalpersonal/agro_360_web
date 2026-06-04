<?php

namespace App\Services\Reports;

use App\Models\OfficialReport;
use App\Models\User;
use App\Services\Exporters\SiexCsvExporter;
use App\Services\Exporters\SiexXmlExporter;
use Illuminate\Support\Facades\Log;

class ReportExporter
{
    public function __construct(
        private SiexCsvExporter $csvExporter,
        private SiexXmlExporter $xmlExporter
    ) {}

    /**
     * Generar exportaciones CSV y XML para un informe
     *
     * @param mixed $data
     */
    public function exportReport(
        OfficialReport $report,
        User $user,
        $data,
        array $stats,
        string $type
    ): void {
        try {
            // Generar CSV
            $csvPath = $type === 'phytosanitary'
                ? $this->csvExporter->exportPhytosanitaryTreatments($report, $user, $data, $stats)
                : $this->csvExporter->exportFullNotebook($report, $user, $data, $stats);

            // Generar XML
            $xmlPath = $type === 'phytosanitary'
                ? $this->xmlExporter->exportPhytosanitaryTreatments($report, $user, $data, $stats)
                : $this->xmlExporter->exportFullNotebook($report, $user, $data, $stats);

            // Actualizar rutas en el reporte
            $report->update([
                'csv_path' => $csvPath,
                'xml_path' => $xmlPath,
            ]);

            Log::info('Exportaciones CSV/XML generadas exitosamente', [
                'report_id' => $report->id,
                'csv_path' => $csvPath,
                'xml_path' => $xmlPath,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar exportaciones', [
                'report_id' => $report->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            // No lanzar excepción - las exportaciones son opcionales
        }
    }
}
