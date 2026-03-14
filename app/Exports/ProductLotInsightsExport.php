<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductLotInsightsExport implements FromArray, WithStyles, WithTitle
{
    public function __construct(
        protected array  $pivot,
        protected string $metric,
    ) {}

    public function array(): array
    {
        $months  = $this->pivot['months'];
        $lots    = $this->pivot['lots'];
        $colTots = $this->pivot['colTotals'];

        $monthLabels = array_map(fn ($m) => $this->formatMonth($m), $months);

        $rows[] = array_merge(['Lote de producto'], $monthLabels, ['TOTAL']);

        foreach ($lots as $lot) {
            $row = [$lot['name']];
            foreach ($months as $month) {
                $row[] = $this->fmt($lot['months'][$month] ?? 0);
            }
            $row[] = $this->fmt($lot['total']);
            $rows[] = $row;
        }

        $totRow = ['TOTAL'];
        foreach ($months as $month) {
            $totRow[] = $this->fmt($colTots[$month] ?? 0);
        }
        $totRow[] = $this->fmt($this->pivot['grandTotal']);
        $rows[] = $totRow;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->pivot['lots']) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ede9fe']],
            ],
            'A1:A' . $lastRow => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Insights Lotes';
    }

    private function formatMonth(string $ym): string
    {
        [$year, $month] = explode('-', $ym);
        $names = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return ($names[(int) $month] ?? $month) . ' ' . $year;
    }

    private function fmt(float $val): string
    {
        return $this->metric === 'amount'
            ? number_format($val, 2, '.', '')
            : number_format($val, 0, '.', '');
    }
}
