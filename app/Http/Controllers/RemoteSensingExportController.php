<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Services\RemoteSensing\ExportService;
use App\Exports\RemoteSensingExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class RemoteSensingExportController extends Controller
{
    use AuthorizesRequests;
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export remote sensing data to PDF
     */
    public function exportPdf(Request $request, Plot $plot)
    {
        $this->authorize('view', $plot);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $pdf = $this->exportService->exportToPdf($plot, $startDate, $endDate);

        $filename = 'teledeteccion_' . str_replace(' ', '_', $plot->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export remote sensing data to Excel
     */
    public function exportExcel(Request $request, Plot $plot)
    {
        $this->authorize('view', $plot);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $filename = 'teledeteccion_' . str_replace(' ', '_', $plot->name) . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new RemoteSensingExport($plot, $startDate, $endDate), $filename);
    }
}
