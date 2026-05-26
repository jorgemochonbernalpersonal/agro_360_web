<?php

namespace App\Exports;

use App\Models\ProductStockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovementsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $userId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct(int $userId, $dateFrom = null, $dateTo = null)
    {
        $this->userId = $userId;
        $this->dateFrom = $dateFrom ?? now()->subMonth();
        $this->dateTo = $dateTo ?? now();
    }

    public function collection()
    {
        return ProductStockMovement::where('user_id', $this->userId)
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->with(['stock.product', 'treatment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('Fecha'),
            __('Producto'),
            __('Tipo de Movimiento'),
            __('Cantidad'),
            __('Cantidad Antes'),
            __('Cantidad Después'),
            __('Precio Unitario (€)'),
            __('Costo Total (€)'),
            __('Tratamiento'),
            __('Notas'),
        ];
    }

    public function map($movement): array
    {
        $typeLabels = [
            'purchase' => __('Compra'),
            'consumption' => __('Consumo'),
            'adjustment' => __('Ajuste'),
            'loss' => __('Pérdida'),
        ];

        return [
            $movement->created_at->format('d/m/Y H:i'),
            $movement->stock->product->name ?? '-',
            $typeLabels[$movement->movement_type] ?? $movement->movement_type,
            $movement->quantity_change,
            $movement->quantity_before,
            $movement->quantity_after,
            $movement->unit_price ?? 0,
            abs($movement->quantity_change) * ($movement->unit_price ?? 0),
            $movement->treatment ? "Tratamiento #{$movement->treatment->id}" : '-',
            $movement->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2196F3']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Movimientos';
    }
}
