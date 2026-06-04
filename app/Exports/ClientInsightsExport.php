<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientInsightsExport implements FromArray, WithStyles, WithTitle
{
    public function __construct(
        protected array $pivot,
        protected string $metric,
    ) {}

    public function array(): array
    {
        $months = $this->pivot['months'];
        $clients = $this->pivot['clients'];
        $colTots = $this->pivot['colTotals'];

        $monthLabels = array_map(fn ($m) => $this->formatMonth($m), $months);

        // Cabecera
        $rows[] = array_merge(['Cliente'], $monthLabels, ['TOTAL']);

        // Filas de datos
        foreach ($clients as $client) {
            $row = [$client['name']];
            foreach ($months as $month) {
                $row[] = $this->fmt($client['months'][$month] ?? 0);
            }
            $row[] = $this->fmt($client['total']);
            $rows[] = $row;
        }

        // Fila de totales
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
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            count($this->pivot['months']) + 2
        );
        $lastRow = count($this->pivot['clients']) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '16a34a']],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dcfce7']],
            ],
            'A1:A'.$lastRow => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Insights Clientes';
    }

    private function formatMonth(string $ym): string
    {
        [$year, $month] = explode('-', $ym);
        $names = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        return ($names[(int) $month] ?? $month).' '.$year;
    }

    private function fmt(float $val): string
    {
        return $this->metric === 'amount'
            ? number_format($val, 2, '.', '')
            : number_format($val, 0, '.', '');
    }
}
