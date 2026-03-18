<?php

namespace App\Http\Controllers\Winery;

use App\Http\Controllers\Controller;
use App\Models\Wine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WineTraceabilityController extends Controller
{
    public function exportPdf(Wine $wine): Response
    {
        if ($wine->user_id !== Auth::id()) {
            abort(403);
        }

        $wine->load([
            'oenologist',
            'wineHarvests.harvest',
            'processDetails'       => fn($q) => $q->orderBy('start_date'),
            'fermentationControls' => fn($q) => $q->orderBy('control_date'),
            'analyses'             => fn($q) => $q->orderBy('analysis_date'),
            'losses'               => fn($q) => $q->orderBy('loss_date'),
            'bottlings',
            'labelings.labelBatch',
        ]);

        $pdf = Pdf::loadView('pdf.wine-traceability', compact('wine'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'    => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'trazabilidad-' . str($wine->name)->slug() . '-' . ($wine->vintage ?? 'sv') . '.pdf';

        return $pdf->download($filename);
    }
}
