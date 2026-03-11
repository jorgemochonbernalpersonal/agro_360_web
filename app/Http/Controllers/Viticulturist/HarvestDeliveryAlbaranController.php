<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\HarvestDelivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class HarvestDeliveryAlbaranController extends Controller
{
    public function __invoke(HarvestDelivery $delivery): Response
    {
        abort_unless($delivery->viticulturist_id === Auth::id(), 403);

        $delivery->load([
            'viticulturist.profile.province',
            'viticulturist.profile.municipality',
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'harvest.winery',
        ]);

        $pdf = Pdf::loadView('reports.viticulturist-harvest-delivery-albaran', [
            'delivery' => $delivery,
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $date     = $delivery->delivery_date?->format('d-m-Y') ?? now()->format('d-m-Y');
        $ticket   = $delivery->ticket_number ? '_' . str_replace(['/', ' '], '-', $delivery->ticket_number) : '';
        $filename = 'albaran_entrega' . $ticket . '_' . $date . '.pdf';

        return $pdf->download($filename);
    }
}
