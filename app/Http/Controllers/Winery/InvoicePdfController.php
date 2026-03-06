<?php

namespace App\Http\Controllers\Winery;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoicePdfController extends Controller
{
    /**
     * Descarga el PDF de la factura (wine_sale o grape_purchase).
     */
    public function invoice(string $type, int $id): Response
    {
        $invoice = $this->loadInvoice($type, $id);

        $pdf = Pdf::loadView('reports.invoice', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = 'factura_' . str_replace(['/', '\\', ' '], '-', $invoice->invoice_number ?? $invoice->id) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Descarga el PDF del albarán valorado (con precios, sin valor fiscal).
     */
    public function valoradoNote(string $type, int $id): Response
    {
        $invoice = $this->loadInvoice($type, $id);

        $pdf = Pdf::loadView('reports.delivery-note-valorado', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $code     = $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id;
        $filename = 'albaran_valorado_' . str_replace(['/', '\\', ' '], '-', $code) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Descarga el PDF del albarán / liquidación.
     */
    public function deliveryNote(string $type, int $id): Response
    {
        $invoice = $this->loadInvoice($type, $id);

        $pdf = Pdf::loadView('reports.delivery-note', [
            'invoice' => $invoice,
            'user'    => $invoice->user->load('profile.province'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $code     = $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id;
        $filename = 'albaran_' . str_replace(['/', '\\', ' '], '-', $code) . '.pdf';

        return $pdf->download($filename);
    }

    // -------------------------------------------------------------------------

    private function loadInvoice(string $type, int $id): Invoice
    {
        return Invoice::where('user_id', Auth::id())
            ->where('invoice_type', $type)
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
