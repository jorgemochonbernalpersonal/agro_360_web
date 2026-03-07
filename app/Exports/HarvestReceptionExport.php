<?php

namespace App\Exports;

use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HarvestReceptionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int    $wineryId,
        protected string $campaignFilter     = '',
        protected string $viticulturistFilter= '',
        protected string $disqualifiedFilter = '',
    ) {}

    public function collection()
    {
        $viticulturistIds = WineryViticulturist::where('winery_id', $this->wineryId)->pluck('viticulturist_id');
        $campaignIds      = Campaign::forViticulturist($this->wineryId)->pluck('id');

        $query = Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ])->whereHas('activity', fn(Builder $q) =>
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $campaignIds)
        );

        if ($this->campaignFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('campaign_id', $this->campaignFilter)
            );
        }

        if ($this->viticulturistFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('viticulturist_id', $this->viticulturistFilter)
            );
        }

        if ($this->disqualifiedFilter !== '') {
            $query->where('disqualified', (bool) $this->disqualifiedFilter);
        }

        return $query->orderByDesc('harvest_start_date')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Añada',
            'Viticultor',
            'Parcela',
            'Variedad',
            'Fecha',
            'Hora',
            'Ticket',
            'Kg recibidos',
            'Kg/ha',
            'Precio €/kg',
            'Valor total €',
            'Alcohol pot. %',
            'Baumé °Bé',
            'Brix °Bx',
            'Acidez g/L',
            'pH',
            'Estado sanitario',
            'Matrícula',
            'Doc. transporte',
            'Depósito',
            'Descartada',
            'Motivo descarte',
            'Estado',
            'Notas',
        ];
    }

    public function map($harvest): array
    {
        $sanitaryLabels = [
            'sano'          => 'Sano',
            'daño_leve'     => 'Daño leve',
            'daño_moderado' => 'Daño moderado',
            'daño_grave'    => 'Daño grave',
        ];

        return [
            $harvest->id,
            $harvest->activity?->campaign?->year ?? '—',
            $harvest->activity?->viticulturist?->name ?? '—',
            $harvest->plotPlanting?->plot?->name ?? '—',
            $harvest->plotPlanting?->grapeVariety?->name ?? '—',
            $harvest->harvest_start_date?->format('d/m/Y') ?? '—',
            $harvest->harvest_time ?? '—',
            $harvest->harvest_ticket_number ?? '—',
            (float) $harvest->total_weight,
            $harvest->yield_per_hectare ? (float) $harvest->yield_per_hectare : '—',
            $harvest->price_per_kg ? (float) $harvest->price_per_kg : '—',
            $harvest->total_value ? (float) $harvest->total_value : '—',
            $harvest->potential_alcohol ? (float) $harvest->potential_alcohol : '—',
            $harvest->baume_degree ? (float) $harvest->baume_degree : '—',
            $harvest->brix_degree ? (float) $harvest->brix_degree : '—',
            $harvest->acidity_level ? (float) $harvest->acidity_level : '—',
            $harvest->ph_level ? (float) $harvest->ph_level : '—',
            $sanitaryLabels[$harvest->health_status] ?? '—',
            $harvest->vehicle_plate ?? '—',
            $harvest->transport_document_number ?? '—',
            $harvest->container?->name ?? '—',
            $harvest->disqualified ? 'Sí' : 'No',
            $harvest->disqualified_reason ?? '—',
            $harvest->status === 'cancelled' ? 'Anulada' : 'Activa',
            $harvest->notes ?? '—',
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
        return 'Recepciones ' . now()->format('d-m-Y');
    }
}
