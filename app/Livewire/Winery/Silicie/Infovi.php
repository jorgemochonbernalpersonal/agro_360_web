<?php

namespace App\Livewire\Winery\Silicie;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * INFOVI — Sistema de Información de Mercados del Sector Vitivinícola
 *
 * Governed by Real Decreto 739/2015 (MAPA/AICA).
 * Producers must declare monthly: stocks, production, and sales
 * broken down by wine type, colour, and designation of origin (DO).
 *
 * This component generates the data in HL (hectolitres) needed to fill
 * the official AICA declarations at mapa.gob.es/infovi.
 */
class Infovi extends Component
{
    public string $filterYear  = '';
    public string $filterMonth = '';

    // INFOVI wine type codes (AICA nomenclature)
    const WINE_CATEGORIES = [
        'red'       => 'Vino tinto tranquilo',
        'white'     => 'Vino blanco tranquilo',
        'rose'      => 'Vino rosado tranquilo',
        'sparkling'  => 'Vino espumoso',
        'fortified'  => 'Vino licoroso / generoso',
        'sweet'      => 'Vino dulce natural',
        'semi_sweet' => 'Vino semidulce',
        'other'      => 'Otros vinos',
    ];

    public function mount(): void
    {
        $this->filterYear  = (string) now()->year;
        $this->filterMonth = (string) now()->month;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();
        $year     = (int) ($this->filterYear  ?: now()->year);
        $month    = (int) ($this->filterMonth ?: now()->month);

        $existencias = $this->buildCuadroExistencias($wineryId, $year, $month);
        $produccion  = $this->buildCuadroProduccion($wineryId, $year);
        $ventas      = $this->buildCuadroVentas($wineryId, $year);
        $entradas    = $this->buildCuadroEntradas($wineryId, $year);

        return view('livewire.winery.silicie.infovi', compact(
            'existencias', 'produccion', 'ventas', 'entradas', 'year', 'month'
        ));
    }

    /**
     * Cuadro de existencias: stock actual por tipo y color de vino, en HL.
     * Se calcula desde container_current_states → wines, agrupado por wine_type.
     */
    private function buildCuadroExistencias(int $wineryId, int $year, int $month): array
    {
        // Use last snapshot for the given month if available, otherwise live stock
        $snapshotDate = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->whereYear('snapshot_date', $year)
            ->whereMonth('snapshot_date', $month)
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if ($snapshotDate) {
            $rows = DB::table('wine_stock_snapshots as wss')
                ->where('wss.user_id', $wineryId)
                ->where('wss.snapshot_date', $snapshotDate)
                ->select([
                    'wss.wine_type',
                    DB::raw('SUM(wss.quantity_liters) / 100 as hl'),
                    DB::raw('COUNT(DISTINCT wss.wine_id) as wine_count'),
                ])
                ->groupBy('wss.wine_type')
                ->get();
        } else {
            $rows = DB::table('container_current_states as ccs')
                ->join('containers as c', 'c.id', '=', 'ccs.container_id')
                ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
                ->where('c.user_id', $wineryId)
                ->where('ccs.current_quantity', '>', 0)
                ->select([
                    'w.wine_type',
                    DB::raw('SUM(ccs.current_quantity) / 100 as hl'),
                    DB::raw('COUNT(DISTINCT w.id) as wine_count'),
                ])
                ->groupBy('w.wine_type')
                ->get();
        }

        $result = [];
        $total  = 0;

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $row = $rows->firstWhere('wine_type', $type);
            $hl  = $row ? round((float) $row->hl, 3) : 0;
            $result[] = [
                'type'       => $type,
                'label'      => $label,
                'hl'         => $hl,
                'wine_count' => $row->wine_count ?? 0,
                'source'     => $snapshotDate ? 'snapshot' : 'live',
            ];
            $total += $hl;
        }

        return [
            'rows'          => $result,
            'total_hl'      => round($total, 3),
            'snapshot_date' => $snapshotDate,
        ];
    }

    /**
     * Cuadro de producción anual: vinos obtenidos en el año (añada = year),
     * en HL, por tipo.
     */
    private function buildCuadroProduccion(int $wineryId, int $year): array
    {
        $rows = DB::table('wines as w')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $year)
            ->whereNotIn('w.status', ['cancelled'])
            ->whereNotNull('w.volume_liters')
            ->where('w.volume_liters', '>', 0)
            ->select([
                'w.wine_type',
                DB::raw('SUM(w.volume_liters) / 100 as hl'),
                DB::raw('COUNT(*) as wine_count'),
            ])
            ->groupBy('w.wine_type')
            ->get();

        // Also include wine from fermentation/bottling wine_process_details
        $result = [];
        $total  = 0;

        foreach (self::WINE_CATEGORIES as $type => $label) {
            $row = $rows->firstWhere('wine_type', $type);
            $hl  = $row ? round((float) $row->hl, 3) : 0;
            $result[] = [
                'type'       => $type,
                'label'      => $label,
                'hl'         => $hl,
                'wine_count' => $row->wine_count ?? 0,
            ];
            $total += $hl;
        }

        return [
            'rows'     => $result,
            'total_hl' => round($total, 3),
        ];
    }

    /**
     * Cuadro de ventas (salidas por facturación).
     *
     * invoice_items does not store wine_id or quantity_liters, so we report
     * invoice-level totals. The HL breakdown by wine type is not available
     * unless invoice items are linked to wines — this is a known gap for INFOVI.
     */
    private function buildCuadroVentas(int $wineryId, int $year): array
    {
        $invoices = DB::table('invoices as i')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereYear('i.invoice_date', $year)
            ->leftJoin('clients as cl', 'cl.id', '=', 'i.client_id')
            ->select([
                'i.id',
                'i.invoice_date',
                'i.invoice_number',
                'i.total_amount',
                DB::raw("COALESCE(cl.company_name, CONCAT(COALESCE(cl.first_name,''),' ',COALESCE(cl.last_name,''))) as client_name"),
            ])
            ->orderByDesc('i.invoice_date')
            ->get();

        return [
            'invoices'     => $invoices,
            'total_amount' => round((float) $invoices->sum('total_amount'), 2),
            'count'        => $invoices->count(),
            'note'         => 'El desglose por HL y tipo de vino requiere vincular líneas de factura a productos concretos.',
        ];
    }

    /**
     * Cuadro de entradas: uva propia y comprada, mosto y vino a granel recibido
     * en el año, en KG (uva) y HL (mosto/vino).
     */
    private function buildCuadroEntradas(int $wineryId, int $year): array
    {
        // Uva propia (harvests)
        $kgPropia = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->where('vintage', $year)
            ->sum('total_weight');

        // Entradas externas por tipo
        $externas = DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('vintage_year', $year)
            ->select([
                'grape_type',
                DB::raw('SUM(total_weight_kg) as total_kg'),
                DB::raw('COUNT(*) as entries'),
            ])
            ->groupBy('grape_type')
            ->get();

        return [
            'kg_propia'       => (float) $kgPropia,
            'kg_comprada'     => (float) ($externas->firstWhere('grape_type', 'grapes')->total_kg ?? 0),
            'kg_mosto'        => (float) ($externas->firstWhere('grape_type', 'must')->total_kg ?? 0),
            'kg_vino_granel'  => (float) ($externas->firstWhere('grape_type', 'bulk_wine')->total_kg ?? 0),
        ];
    }
}
