<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\PacDeclaration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PacDeclarationPdfController extends Controller
{
    public function download(PacDeclaration $declaration)
    {
        if ($declaration->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        $declaration->load(['viticulturist', 'items.plot.municipality']);

        app()->setLocale('es');
        $pdf = Pdf::loadView('reports.pac-declaration', [
            'declaration' => $declaration,
            'viticulturist' => $declaration->viticulturist,
            'ecoSchemesMap' => PacDeclaration::ECO_SCHEMES,
        ])
            ->setPaper('A4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = 'declaracion_pac_'.$declaration->year.'.pdf';

        return $pdf->download($filename);
    }
}
