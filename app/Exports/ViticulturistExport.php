<?php

namespace App\Exports;

use App\Models\WineryViticulturist;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ViticulturistExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int $wineryId,
    ) {}

    public function collection(): Collection
    {
        return WineryViticulturist::where('winery_id', $this->wineryId)
            ->with(['viticulturist' => fn ($q) => $q->withCount('plots')])
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('Nombre'),
            __('Email'),
            __('Origen'),
            __('Puede acceder'),
            __('Nº Parcelas'),
            __('Vinculado el'),
        ];
    }

    public function map($relation): array
    {
        $v = $relation->viticulturist;

        $sourceMap = [
            'own' => __('Propio'),
            'supervisor' => __('Del supervisor'),
            'viticulturist' => __('Por invitación'),
            'self' => __('Autogestionado'),
        ];

        return [
            $v?->name ?? '—',
            $v?->email ?? '—',
            $sourceMap[$relation->source] ?? $relation->source,
            $v?->can_login ? 'Sí' : 'No',
            $v?->plots_count ?? 0,
            $relation->created_at?->format('d/m/Y') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16a34a'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Viticultores';
    }
}
