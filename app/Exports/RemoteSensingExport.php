<?php

namespace App\Exports;

use App\Models\Plot;
use App\Services\RemoteSensing\ExportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RemoteSensingExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected Plot $plot;
    protected ?Carbon $startDate;
    protected ?Carbon $endDate;

    public function __construct(Plot $plot, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->plot = $plot;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $service = new ExportService();
        return $service->getExcelData($this->plot, $this->startDate, $this->endDate);
    }

    public function headings(): array
    {
        return [
            __('Fecha'),
            'NDVI',
            __('NDVI Mín'),
            __('NDVI Máx'),
            'NDWI',
            __('Estado'),
            __('Tendencia'),
            __('Temperatura (°C)'),
            __('Precipitación (mm)'),
            __('Humedad (%)'),
            __('Humedad Suelo (%)'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '2E7D32'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Datos Teledetección - ' . $this->plot->name;
    }
}
