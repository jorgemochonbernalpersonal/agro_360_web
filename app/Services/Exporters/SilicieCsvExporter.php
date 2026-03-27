<?php

namespace App\Services\Exporters;

use Illuminate\Support\Facades\DB;

/**
 * Genera el libro de movimientos SILICIE (Suministro Inmediato de Libros
 * de Información Contable de Impuestos Especiales) en formato CSV semicolon,
 * siguiendo la estructura de la Orden HAC/1505/2024 y los tipos de movimiento
 * publicados por la AEAT para elaboradores de vino y bebidas fermentadas.
 *
 * El CSV resultante puede importarse en software homologado (Bacosoft LEX,
 * ISAGRI Vino...) que genera el XML final para envío a sede.agenciatributaria.gob.es
 */
class SilicieCsvExporter
{
    // ── Tipos de movimiento AEAT (Libro de movimientos) ───────────────────
    const MOVEMENT_TYPES = [
        'A02' => 'Entrada interior',
        'A04' => 'Entrada desde la UE',
        'A06' => 'Entrada por importación',
        'A08' => 'Salida interior',
        'A09' => 'Salida a Canarias',
        'A10' => 'Salida a la UE',
        'A11' => 'Salida por exportación',
        'A14' => 'Autoconsumo',
        'A15' => 'Fabricado/Obtenido',
        'A21' => 'Salida almacén auxiliar',
        'A22' => 'Entrada almacén auxiliar',
        'A28' => 'Destrucción',
        'A29' => 'Pérdida art. 6.2 LIE',
        'A30' => 'Diferencia en menos',
        'A32' => 'Diferencia en menos en fabricación',
    ];

    // ── Códigos NC para productos del sector vitivinícola ─────────────────
    // 4 dígitos = no sujeto; 8 dígitos = sujeto impuesto especial
    const NC_CODES = [
        'grapes'      => '0806',     // Uva fresca — no sujeta
        'must'        => '22043096', // Mosto de uva parcialmente fermentado
        'bulk_wine'   => '22042199', // Vino tranquilo >2L a granel
        'red_wine'    => '22042199', // Vino tinto >2L
        'white_wine'  => '22042199', // Vino blanco >2L
        'rose_wine'   => '22042199', // Vino rosado >2L
        'sparkling'   => '22041000', // Vino espumoso
        'fortified'   => '22042199', // Vino licoroso
        'orujo'       => '2308',     // Orujo de uva — no sujeto
        'lias'        => '2307',     // Lías de vino — no sujeto
        'vinaza'      => '2307',     // Vinaza — asimilado lías
    ];

    // ── Unidades SILICIE ───────────────────────────────────────────────────
    const UNIT_HL   = 'HL';  // Hectolitros (vino, mosto)
    const UNIT_KG   = 'KG';  // Kilogramos (uva, subproductos sólidos)

    // ── Tipo de documento SILICIE 2.0 (Orden HAC/1505/2024) ──────────────
    // Mandatory since 01/01/2025
    const DOCUMENT_TYPE_MAP = [
        'A02' => 'ALB', // Albarán de recepción
        'A04' => 'ALB',
        'A06' => 'ALB',
        'A08' => 'FAC', // Factura de venta
        'A09' => 'FAC',
        'A10' => 'FAC',
        'A11' => 'FAC',
        'A14' => 'OTR', // Otros (autoconsumo, producción, pérdidas...)
        'A15' => 'OTR',
        'A21' => 'OTR',
        'A22' => 'OTR',
        'A28' => 'OTR',
        'A29' => 'OTR',
        'A30' => 'OTR',
        'A32' => 'OTR',
    ];

    // ── Cabecera CSV oficial (Orden HAC/1505/2024 — SILICIE 2.0) ─────────
    const HEADERS = [
        'TIPO_MOVIMIENTO',
        'FECHA_OPERACION',
        'TIPO_DOCUMENTO',    // NEW in SILICIE 2.0: FAC | ALB | DAA | CMR | OTR
        'PERIODO_FISCAL',    // NEW in SILICIE 2.0: YYYY-MM (period of invoice/document)
        'CODIGO_NC',
        'DESCRIPCION_PRODUCTO',
        'CANTIDAD',
        'UNIDAD_MEDIDA',
        'ORIGEN_DESTINO_NIF',
        'ORIGEN_DESTINO_NOMBRE',
        'NUM_DOCUMENTO',
        'REFERENCIA_INTERNA',
        'OBSERVACIONES',
    ];

    public function export(int $wineryId, int $vintage): string
    {
        $rows = [];

        // ── A02: Entradas de uva propia (Recepciones de vendimia) ─────────
        $harvests = DB::table('harvests as h')
            ->where('h.winery_id', $wineryId)
            ->where('h.vintage', $vintage)
            ->leftJoin('grape_reception_batches as grb', 'grb.id', '=', 'h.batch_id')
            ->leftJoin('users as viti', 'viti.id', '=', 'grb.viticulturist_id')
            ->select(['h.harvest_start_date', 'h.total_weight', 'h.id', 'viti.name as viti_name'])
            ->get();

        foreach ($harvests as $h) {
            $rows[] = [
                'TIPO_MOVIMIENTO'       => 'A02',
                'FECHA_OPERACION'       => $this->formatDate($h->harvest_start_date),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP['A02'],
                'PERIODO_FISCAL'        => $this->fiscalPeriod($h->harvest_start_date),
                'CODIGO_NC'             => self::NC_CODES['grapes'],
                'DESCRIPCION_PRODUCTO'  => 'Uva fresca - vendimia propia',
                'CANTIDAD'              => $this->formatQty((float) ($h->total_weight ?? 0)),
                'UNIDAD_MEDIDA'         => self::UNIT_KG,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => $h->viti_name ?? '',
                'NUM_DOCUMENTO'         => 'REC-' . $h->id,
                'REFERENCIA_INTERNA'    => 'HARVEST-' . $h->id,
                'OBSERVACIONES'         => 'Vendimia ' . $vintage,
            ];
        }

        // ── A02/A04/A06: Entradas de uva/mosto/vino comprado ──────────────
        $external = DB::table('external_grapes')
            ->where('user_id', $wineryId)
            ->where('vintage_year', $vintage)
            ->leftJoin('grape_varieties as gv', 'gv.id', '=', 'external_grapes.grape_variety_id')
            ->select([
                'external_grapes.id',
                'external_grapes.grape_type',
                'external_grapes.supplier_name',
                'external_grapes.total_weight_kg',
                'external_grapes.entry_date',
                'external_grapes.protection_level',
                'gv.name as variety_name',
            ])
            ->get();

        foreach ($external as $eg) {
            $ncCode      = self::NC_CODES[$eg->grape_type] ?? self::NC_CODES['bulk_wine'];
            $description = match ($eg->grape_type) {
                'grapes'    => 'Uva comprada - ' . ($eg->variety_name ?? 'sin variedad'),
                'must'      => 'Mosto comprado - ' . ($eg->protection_level ?? 'sin DO'),
                'bulk_wine' => 'Vino a granel comprado - ' . ($eg->protection_level ?? 'sin DO'),
                default     => 'Entrada exterior',
            };

            $rows[] = [
                'TIPO_MOVIMIENTO'       => 'A02',
                'FECHA_OPERACION'       => $this->formatDate($eg->entry_date),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP['A02'],
                'PERIODO_FISCAL'        => $this->fiscalPeriod($eg->entry_date),
                'CODIGO_NC'             => $ncCode,
                'DESCRIPCION_PRODUCTO'  => $description,
                'CANTIDAD'              => $this->formatQty((float) $eg->total_weight_kg),
                'UNIDAD_MEDIDA'         => self::UNIT_KG,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => $eg->supplier_name ?? '',
                'NUM_DOCUMENTO'         => 'EXT-' . $eg->id,
                'REFERENCIA_INTERNA'    => 'EXTGRAPE-' . $eg->id,
                'OBSERVACIONES'         => ucfirst($eg->grape_type) . ' - ' . ($eg->protection_level ?? ''),
            ];
        }

        // ── A15: Producción — vinos obtenidos ─────────────────────────────
        $wines = DB::table('wines')
            ->where('user_id', $wineryId)
            ->where('vintage', $vintage)
            ->whereNotIn('status', ['cancelled'])
            ->select(['id', 'name', 'wine_type', 'volume_liters', 'status', 'trace_token'])
            ->get();

        foreach ($wines as $w) {
            if (! $w->volume_liters) {
                continue;
            }
            $ncCode      = $this->ncCodeForWineType($w->wine_type);
            $description = $this->wineDescription($w->wine_type) . ' - ' . ($w->name ?? 'Vino ' . $w->wine_type);

            $productionDate = now()->year($vintage)->endOfYear()->format('Y-m-d');
            $rows[] = [
                'TIPO_MOVIMIENTO'       => 'A15',
                'FECHA_OPERACION'       => $this->formatDate($productionDate),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP['A15'],
                'PERIODO_FISCAL'        => $this->fiscalPeriod($productionDate),
                'CODIGO_NC'             => $ncCode,
                'DESCRIPCION_PRODUCTO'  => $description,
                'CANTIDAD'              => $this->formatQty($w->volume_liters / 100), // L→HL
                'UNIDAD_MEDIDA'         => self::UNIT_HL,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => 'Producción propia',
                'NUM_DOCUMENTO'         => 'WINE-' . $w->id,
                'REFERENCIA_INTERNA'    => $w->trace_token ?? ('WINE-' . $w->id),
                'OBSERVACIONES'         => 'Añada ' . $vintage,
            ];
        }

        // ── A08: Salidas — ventas facturadas ──────────────────────────────
        $invoices = DB::table('invoices as i')
            ->where('i.user_id', $wineryId)
            ->whereIn('i.status', ['sent', 'paid'])
            ->whereYear('i.invoice_date', $vintage)
            ->leftJoin('clients as cl', 'cl.id', '=', 'i.client_id')
            ->select([
                'i.id', 'i.invoice_date', 'i.invoice_number', 'i.total_amount',
                \Illuminate\Support\Facades\DB::raw("COALESCE(cl.company_name, CONCAT(COALESCE(cl.first_name,''),' ',COALESCE(cl.last_name,''))) as client_name"),
            ])
            ->get();

        foreach ($invoices as $inv) {
            $rows[] = [
                'TIPO_MOVIMIENTO'       => 'A08',
                'FECHA_OPERACION'       => $this->formatDate($inv->invoice_date),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP['A08'],
                'PERIODO_FISCAL'        => $this->fiscalPeriod($inv->invoice_date),
                'CODIGO_NC'             => self::NC_CODES['bulk_wine'],
                'DESCRIPCION_PRODUCTO'  => 'Venta de vino / producto',
                'CANTIDAD'              => '',   // Requiere items detallados
                'UNIDAD_MEDIDA'         => self::UNIT_HL,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => $inv->client_name ?? '',
                'NUM_DOCUMENTO'         => $inv->invoice_number ?? ('FAC-' . $inv->id),
                'REFERENCIA_INTERNA'    => 'INV-' . $inv->id,
                'OBSERVACIONES'         => 'Factura ' . ($inv->invoice_number ?? $inv->id),
            ];
        }

        // ── A29: Pérdidas (mermas, evaporación, filtración) ───────────────
        $losses = DB::table('wine_losses as wl')
            ->join('wines as w', 'w.id', '=', 'wl.wine_id')
            ->where('w.user_id', $wineryId)
            ->where('w.vintage', $vintage)
            ->select(['wl.id', 'wl.loss_date', 'wl.loss_type', 'wl.quantity', 'wl.regulatory_reference', 'w.name as wine_name', 'w.wine_type'])
            ->get();

        foreach ($losses as $l) {
            // A32 para mermas en fabricación (fermentación), A29 para el resto
            $type = in_array($l->loss_type, ['evaporation', 'filtration'])
                ? 'A32'
                : 'A29';

            $rows[] = [
                'TIPO_MOVIMIENTO'       => $type,
                'FECHA_OPERACION'       => $this->formatDate($l->loss_date),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP[$type],
                'PERIODO_FISCAL'        => $this->fiscalPeriod($l->loss_date),
                'CODIGO_NC'             => $this->ncCodeForWineType($l->wine_type),
                'DESCRIPCION_PRODUCTO'  => 'Pérdida - ' . $l->wine_name,
                'CANTIDAD'              => $this->formatQty($l->quantity / 100),
                'UNIDAD_MEDIDA'         => self::UNIT_HL,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => '',
                'NUM_DOCUMENTO'         => $l->regulatory_reference ?? ('LOSS-' . $l->id),
                'REFERENCIA_INTERNA'    => 'LOSS-' . $l->id,
                'OBSERVACIONES'         => 'Tipo: ' . $l->loss_type,
            ];
        }

        // ── A28: Subproductos enviados (orujo→destilería) ─────────────────
        $subproducts = DB::table('wine_subproducts as ws')
            ->where('ws.user_id', $wineryId)
            ->whereYear('ws.subproduct_date', $vintage)
            ->leftJoin('wines as w', 'w.id', '=', 'ws.wine_id')
            ->select(['ws.id', 'ws.subproduct_date', 'ws.type', 'ws.destination', 'ws.destination_name', 'ws.quantity', 'ws.lot_number'])
            ->get();

        foreach ($subproducts as $s) {
            $ncCode = self::NC_CODES[$s->type] ?? self::NC_CODES['orujo'];
            $type   = $s->destination === 'distillery' ? 'A28' : 'A08';

            $rows[] = [
                'TIPO_MOVIMIENTO'       => $type,
                'FECHA_OPERACION'       => $this->formatDate($s->subproduct_date),
                'TIPO_DOCUMENTO'        => self::DOCUMENT_TYPE_MAP[$type] ?? 'OTR',
                'PERIODO_FISCAL'        => $this->fiscalPeriod($s->subproduct_date),
                'CODIGO_NC'             => $ncCode,
                'DESCRIPCION_PRODUCTO'  => 'Subproducto - ' . $s->type,
                'CANTIDAD'              => $this->formatQty($s->quantity / 100),
                'UNIDAD_MEDIDA'         => self::UNIT_KG,
                'ORIGEN_DESTINO_NIF'    => '',
                'ORIGEN_DESTINO_NOMBRE' => $s->destination_name ?? $s->destination,
                'NUM_DOCUMENTO'         => $s->lot_number ?? ('SUB-' . $s->id),
                'REFERENCIA_INTERNA'    => 'SUB-' . $s->id,
                'OBSERVACIONES'         => 'Destino: ' . $s->destination,
            ];
        }

        // ── Generar CSV ───────────────────────────────────────────────────
        // Ordenar por fecha
        usort($rows, fn ($a, $b) => strcmp($a['FECHA_OPERACION'] ?? '', $b['FECHA_OPERACION'] ?? ''));

        return $this->buildCsvContent($rows);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function ncCodeForWineType(string $type): string
    {
        return match ($type) {
            'sparkling'             => self::NC_CODES['sparkling'],
            'fortified', 'sweet'    => self::NC_CODES['fortified'],
            default                 => self::NC_CODES['bulk_wine'],
        };
    }

    private function wineDescription(string $type): string
    {
        return match ($type) {
            'red'        => 'Vino tinto tranquilo',
            'white'      => 'Vino blanco tranquilo',
            'rose'       => 'Vino rosado tranquilo',
            'sparkling'  => 'Vino espumoso',
            'fortified'  => 'Vino licoroso',
            'sweet'      => 'Vino dulce',
            'semi_sweet' => 'Vino semidulce',
            default      => 'Vino (otro)',
        };
    }

    /**
     * Returns the fiscal period (YYYY-MM) for a given date string.
     * Required by SILICIE 2.0 (Orden HAC/1505/2024) since 01/01/2025.
     */
    private function fiscalPeriod(?string $date): string
    {
        if (! $date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m');
        } catch (\Throwable) {
            return '';
        }
    }

    private function formatDate(?string $date): string
    {
        if (! $date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return '';
        }
    }

    private function formatQty(float $qty): string
    {
        return number_format($qty, 3, ',', '');
    }

    private function buildCsvContent(array $rows): string
    {
        $lines = [];
        // UTF-8 BOM + cabecera
        $lines[] = implode(';', self::HEADERS);

        foreach ($rows as $row) {
            $cells = [];
            foreach (self::HEADERS as $col) {
                $cells[] = $this->cleanValue($row[$col] ?? '');
            }
            $lines[] = implode(';', $cells);
        }

        // BOM UTF-8 para compatibilidad con Excel/software español
        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    private function cleanValue(string $value): string
    {
        // Eliminar saltos de línea internos
        $value = str_replace(["\r\n", "\n", "\r"], ' ', $value);
        // Si contiene ; o " → envolver en comillas dobles
        if (str_contains($value, ';') || str_contains($value, '"') || str_contains($value, "\n")) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
