<?php

namespace App\Http\Controllers\Winery;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoicePdfController extends Controller
{
    public function invoice(string $id): Response
    {
        $invoice = $this->loadInvoice($id);

        $pdf = Pdf::loadView('reports.invoice', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $date     = $invoice->invoice_date?->format('d-m-Y') ?? now()->format('d-m-Y');
        $code     = str_replace(['/', '\\', ' '], '-', $invoice->invoice_number ?? $invoice->id);
        $filename = 'factura_' . $code . '_' . $date . '.pdf';

        return $pdf->download($filename);
    }

    public function valoradoNote(string $id): Response
    {
        $invoice = $this->loadInvoice($id);

        $pdf = Pdf::loadView('reports.delivery-note-valorado', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $date     = $invoice->delivery_note_date?->format('d-m-Y') ?? $invoice->invoice_date?->format('d-m-Y') ?? now()->format('d-m-Y');
        $code     = str_replace(['/', '\\', ' '], '-', $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id);
        $filename = 'albaran_valorado_' . $code . '_' . $date . '.pdf';

        return $pdf->download($filename);
    }

    public function deliveryNote(string $id): Response
    {
        $invoice = $this->loadInvoice($id);

        $pdf = Pdf::loadView('reports.delivery-note', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $date     = $invoice->delivery_note_date?->format('d-m-Y') ?? $invoice->invoice_date?->format('d-m-Y') ?? now()->format('d-m-Y');
        $code     = str_replace(['/', '\\', ' '], '-', $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id);
        $filename = 'albaran_' . $code . '_' . $date . '.pdf';

        return $pdf->download($filename);
    }

    // -------------------------------------------------------------------------

    private function loadInvoice(string $id): Invoice
    {
        return Invoice::where('user_id', Auth::id())
            ->with([
                'user.profile.province',
                'client',
                'clientAddress.municipality',
                'clientAddress.province',
                'items.tax',
                'items.wineLot',
                'items.harvest.plotPlanting.grapeVariety',
                'viticulturist',
            ])
            ->findOrFail($id);
    }
}
