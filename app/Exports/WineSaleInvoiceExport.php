<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WineSaleInvoiceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int    $userId,
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function collection(): Collection
    {
        return Invoice::where('user_id', $this->userId)
            ->where('invoice_type', 'wine_sale')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->with(['client', 'items.wineLot'])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nº Factura',
            'Albarán',
            'Fecha',
            'Cliente',
            'Estado',
            'Entrega',
            'Cobro',
            'Regalo',
            'Rectificativa',
            'Productos',
            'Base imponible (€)',
            'IVA (€)',
            'Total (€)',
        ];
    }

    public function map($invoice): array
    {
        $statusMap = ['draft' => 'Borrador', 'sent' => 'Emitida', 'cancelled' => 'Cancelada'];
        $delivMap  = ['pending' => 'Pendiente', 'delivered' => 'Entregada', 'cancelled' => 'Cancelada'];
        $payMap    = ['unpaid' => 'Pendiente', 'partial' => 'Parcial', 'paid' => 'Cobrada'];

        $products = $invoice->items
            ->filter(fn ($i) => $i->concept_type === 'wine')
            ->map(fn ($i) => $i->name . ' ×' . number_format((float) $i->quantity, 0))
            ->implode(', ');

        return [
            $invoice->invoice_number ?? '—',
            $invoice->delivery_note_code ?? '—',
            $invoice->invoice_date?->format('d/m/Y') ?? '—',
            $invoice->client?->full_name ?? '—',
            $statusMap[$invoice->status] ?? $invoice->status,
            $delivMap[$invoice->delivery_status] ?? $invoice->delivery_status,
            $payMap[$invoice->payment_status] ?? $invoice->payment_status,
            $invoice->gift ? 'Sí' : 'No',
            $invoice->corrective ? 'Sí' : 'No',
            $products ?: '—',
            number_format((float) $invoice->tax_base, 2),
            number_format((float) $invoice->tax_amount, 2),
            number_format((float) $invoice->total_amount, 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16a34a'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Facturas Venta';
    }
}
