<?php

namespace App\Exports;

use App\Models\ProductStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function collection()
    {
        return ProductStock::where('user_id', $this->userId)
            ->where('active', true)
            ->with(['product', 'warehouse'])
            ->orderBy('product_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Ingrediente Activo',
            'Lote',
            'Cantidad',
            'Unidad',
            'Stock Mínimo',
            'Precio Unitario (€)',
            'Valor Total (€)',
            'Almacén',
            'Fecha Caducidad',
            'Proveedor',
            'Estado',
        ];
    }

    public function map($stock): array
    {
        $status = 'OK';
        if ($stock->isExpired()) {
            $status = 'Caducado';
        } elseif ($stock->isExpiringSoon()) {
            $status = 'Próximo a caducar';
        } elseif ($stock->quantity < ($stock->minimum_stock ?? 5)) {
            $status = 'Stock bajo';
        }

        return [
            $stock->product->name,
            $stock->product->active_ingredient ?? '-',
            $stock->batch_number ?? '-',
            (float) $stock->quantity,
            $stock->unit,
            $stock->minimum_stock ?? '-',
            $stock->unit_price ?? 0,
            (float) $stock->quantity * ($stock->unit_price ?? 0),
            $stock->warehouse->name ?? '-',
            $stock->expiry_date?->format('d/m/Y') ?? '-',
            $stock->supplier ?? '-',
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Inventario ' . now()->format('d-m-Y');
    }
}
