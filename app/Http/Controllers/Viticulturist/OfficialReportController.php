<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\OfficialReport;
use App\Services\OfficialReportService;
use Illuminate\Support\Facades\Storage;

class OfficialReportController extends Controller
{
    public function download(OfficialReport $report)
    {
        if ($report->user_id !== auth()->id()) {
            abort(403, __('No tienes permiso para descargar este informe.'));
        }

        $service = new OfficialReportService;

        return $service->downloadReport($report);
    }

    public function preview(OfficialReport $report)
    {
        if ($report->user_id !== auth()->id()) {
            abort(403, __('No tienes permiso para ver este informe.'));
        }

        if (! $report->pdfExists()) {
            abort(404, __('El archivo PDF no existe.'));
        }

        $pdfPath = str_starts_with($report->pdf_path, storage_path())
            ? $report->pdf_path
            : Storage::disk('local')->path($report->pdf_path);

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($report->pdf_filename ?? 'informe.pdf').'"',
        ]);
    }
}
