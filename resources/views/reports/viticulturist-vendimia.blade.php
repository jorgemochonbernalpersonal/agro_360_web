<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Resumen de Vendimia {{ $vintageYear }}</title>
    <style>
        @page { margin: 15mm 12mm 20mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8.5pt; color: #1f2937; line-height: 1.4; }

        .header { border-bottom: 2pt solid #7c3aed; padding-bottom: 10pt; margin-bottom: 12pt; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .title    { font-size: 14pt; font-weight: bold; color: #6d28d9; }
        .subtitle { font-size: 8.5pt; color: #6b7280; margin-top: 2pt; }
        .vitic-name { font-size: 10pt; font-weight: bold; color: #1f2937; }

        .stats { display: table; width: 100%; margin-bottom: 12pt; border: 1pt solid #e5e7eb; border-radius: 4pt; }
        .stat-box { display: table-cell; width: 25%; padding: 7pt; text-align: center; border-right: 1pt solid #e5e7eb; }
        .stat-box:last-child { border-right: none; }
        .stat-value { font-size: 13pt; font-weight: bold; color: #6d28d9; }
        .stat-label { font-size: 7pt; color: #6b7280; margin-top: 2pt; }

        table { width: 100%; border-collapse: collapse; margin-top: 4pt; }
        th { background: #6d28d9; color: #fff; font-size: 7.5pt; padding: 4pt 5pt; text-align: left; font-weight: bold; }
        th.right { text-align: right; }
        td { font-size: 7.5pt; padding: 3.5pt 5pt; border-bottom: 0.5pt solid #f3f4f6; vertical-align: middle; }
        td.right { text-align: right; }
        td.center { text-align: center; }
        tr:nth-child(even) td { background: #faf5ff; }

        .badge { display: inline-block; padding: 1pt 5pt; border-radius: 3pt; font-size: 6.5pt; font-weight: bold; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-zinc   { background: #f4f4f5; color: #52525b; }

        .progress-wrap { background: #e5e7eb; border-radius: 4pt; height: 6pt; width: 100%; display: block; }
        .progress-bar  { height: 6pt; border-radius: 4pt; display: block; }
        .exceeded { color: #991b1b; font-weight: bold; }

        .section-title { font-size: 9pt; font-weight: bold; color: #374151; margin: 10pt 0 4pt; border-left: 3pt solid #6d28d9; padding-left: 6pt; }

        .deliveries-table th { background: #4c1d95; }
        .deliveries-table tr:nth-child(even) td { background: #ede9fe; }
        .delivery-matched { color: #166534; }
        .delivery-disputed { color: #92400e; }
        .delivery-pending { color: #6b7280; }
        .strikethrough { text-decoration: line-through; color: #9ca3af; }

        .footer { position: fixed; bottom: 0; left: 12mm; right: 12mm; font-size: 7pt; color: #9ca3af;
                  border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="title">Resumen de Vendimia · Añada {{ $vintageYear }}</div>
            <div class="subtitle">Cuaderno de campo · Entregas declaradas · Recepciones de bodega</div>
            <div class="subtitle" style="margin-top:3pt;">Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</div>
        </div>
        <div class="header-right">
            <div class="vitic-name">{{ $viticulturistName }}</div>
            <div style="font-size:8pt;color:#6b7280;margin-top:2pt;">Agro365</div>
        </div>
    </div>

    {{-- Stats globales --}}
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $totals['plantings'] }}</div>
            <div class="stat-label">Plantaciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($totals['harvest_kg'], 0) }} kg</div>
            <div class="stat-label">Cosechado (cuaderno)</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($totals['declared_kg'], 0) }} kg</div>
            <div class="stat-label">Declarado (entregas)</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($totals['winery_kg'], 0) }} kg</div>
            <div class="stat-label">Confirmado (bodega)</div>
        </div>
    </div>

    {{-- Tabla resumen por plantación --}}
    <div class="section-title">Resumen por plantación</div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Variedad') }}</th>
                <th>{{ __('Parcela') }}</th>
                <th class="right">{{ __('Ha') }}</th>
                <th class="right">{{ __('Cuaderno (kg)') }}</th>
                <th class="right">{{ __('Declarado (kg)') }}</th>
                <th class="right">{{ __('Bodega (kg)') }}</th>
                <th class="right">{{ __('Cupo PAC (kg)') }}</th>
                <th class="right">{{ __('% Cupo') }}</th>
                <th class="right">{{ __('Diferencia') }}</th>
                <th class="center">{{ __('Estado') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $hasDiscrepancy = $row['discrepancy_pct'] !== null && $row['discrepancy_pct'] > 5;
                    $isOk = $row['harvest_kg'] > 0 && $row['winery_kg'] > 0 && !$hasDiscrepancy;
                    $isPending = $row['harvest_kg'] === 0.0 && $row['winery_kg'] === 0.0;
                @endphp
                <tr>
                    <td><strong>{{ $row['variety'] }}</strong></td>
                    <td>{{ $row['plot'] }}</td>
                    <td class="right">{{ $row['area'] ? number_format($row['area'], 2) : '—' }}</td>
                    <td class="right">{{ $row['harvest_kg'] > 0 ? number_format($row['harvest_kg'], 0) : '—' }}</td>
                    <td class="right">{{ $row['declared_kg'] > 0 ? number_format($row['declared_kg'], 0) : '—' }}</td>
                    <td class="right">{{ $row['winery_kg'] > 0 ? number_format($row['winery_kg'], 0) : '—' }}</td>
                    <td class="right">{{ $row['cupo_kg'] ? number_format($row['cupo_kg'], 0) : '—' }}</td>
                    <td class="right">
                        @if($row['cupo_pct'] !== null)
                            @php $barW = min($row['cupo_pct'], 100); $barC = $row['cupo_exceeded'] ? '#ef4444' : ($row['cupo_pct'] >= 80 ? '#f59e0b' : '#16a34a'); @endphp
                            <span class="{{ $row['cupo_exceeded'] ? 'exceeded' : '' }}">{{ $row['cupo_pct'] }}%</span>
                            <span class="progress-wrap" style="margin-top:2pt;">
                                <span class="progress-bar" style="width:{{ $barW }}%;background:{{ $barC }};"></span>
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="right">
                        @if($row['discrepancy_kg'] !== null && $row['discrepancy_kg'] > 0)
                            {{ number_format($row['discrepancy_kg'], 0) }} kg
                            @if($row['discrepancy_pct'] !== null)
                                ({{ $row['discrepancy_pct'] }}%)
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="center">
                        @if($isPending)
                            <span class="badge badge-zinc">{{ __('Pendiente') }}</span>
                        @elseif($isOk)
                            <span class="badge badge-green">{{ __('Coincide') }}</span>
                        @elseif($hasDiscrepancy)
                            <span class="badge badge-amber">{{ __('Diferencia') }}</span>
                        @else
                            <span class="badge badge-zinc">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Detalle de entregas declaradas --}}
    @php
        $allDeliveries = $rows->flatMap(fn($row) => $row['deliveries']->map(fn($d) => [
            'variety'    => $row['variety'],
            'plot'       => $row['plot'],
            'delivery'   => $d,
        ]));
    @endphp

    @if($allDeliveries->count() > 0)
        <div class="section-title" style="margin-top:14pt;">Detalle de entregas declaradas</div>

        <table class="deliveries-table">
            <thead>
                <tr>
                    <th>{{ __('Variedad · Parcela') }}</th>
                    <th>{{ __('Fecha') }}</th>
                    <th>{{ __('Comprador') }}</th>
                    <th>{{ __('Ticket') }}</th>
                    <th class="right">{{ __('Declarado (kg)') }}</th>
                    <th class="right">{{ __('Precio/kg') }}</th>
                    <th class="right">{{ __('Importe') }}</th>
                    <th class="right">{{ __('Diferencia (kg)') }}</th>
                    <th class="center">{{ __('Estado') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allDeliveries as $item)
                    @php $d = $item['delivery']; @endphp
                    <tr>
                        <td class="{{ $d->disqualified ? 'strikethrough' : '' }}">
                            {{ $item['variety'] }} · {{ $item['plot'] }}
                        </td>
                        <td>{{ $d->delivery_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="{{ $d->disqualified ? 'strikethrough' : '' }}">{{ $d->buyer_name }}</td>
                        <td style="font-family:monospace;font-size:7pt;">{{ $d->ticket_number ?? '—' }}</td>
                        <td class="right {{ $d->disqualified ? 'strikethrough' : '' }}">
                            {{ number_format($d->delivered_kg, 0) }}
                        </td>
                        <td class="right">
                            {{ $d->price_per_kg ? number_format($d->price_per_kg, 3) . ' €' : '—' }}
                        </td>
                        <td class="right">
                            {{ $d->total_price ? number_format($d->total_price, 3) . ' €' : '—' }}
                        </td>
                        <td class="right">
                            {{ $d->discrepancy_kg ? number_format($d->discrepancy_kg, 0) : '—' }}
                        </td>
                        <td class="center">
                            @if($d->disqualified)
                                <span class="badge badge-red">{{ __('Descartada') }}</span>
                            @elseif($d->status === 'matched')
                                <span class="badge badge-green delivery-matched">{{ __('Confirmada') }}</span>
                            @elseif($d->status === 'resolved')
                                <span class="badge badge-green delivery-matched">{{ __('Resuelta') }}</span>
                            @elseif($d->status === 'disputed')
                                <span class="badge badge-amber delivery-disputed">{{ __('Diferencia') }}</span>
                            @else
                                <span class="badge badge-zinc delivery-pending">{{ __('Pendiente') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">Agro365 · Cuaderno Digital de Campo</div>
        <div class="footer-right">{{ $viticulturistName }} · Añada {{ $vintageYear }}</div>
    </div>

    {{-- Números de página (DomPDF canvas API) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                if ($pageCount <= 1) return;
                $font  = $fontMetrics->get_font('DejaVu Sans', 'normal');
                $size  = 6.5;
                $text  = "Página $pageNumber de $pageCount";
                $width = $fontMetrics->get_text_width($text, $font, $size);
                // A4 landscape: 841.89 × 595.28 pt — right margin 12mm ≈ 34 pt, bottom ≈ 14 pt
                $canvas->text(841.89 - 34 - $width, 581, $text, $font, $size, [0.6, 0.6, 0.6]);
            });
        }
    </script>

</body>
</html>
