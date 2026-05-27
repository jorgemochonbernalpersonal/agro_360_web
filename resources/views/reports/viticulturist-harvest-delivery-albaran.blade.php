<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Albarán de Entrega</title>
    <style>
        @page { margin: 18mm 15mm 22mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9pt; color: #1f2937; line-height: 1.5; }

        /* Header */
        .header { display: table; width: 100%; border-bottom: 2pt solid #7c3aed; padding-bottom: 10pt; margin-bottom: 14pt; }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .doc-title  { font-size: 16pt; font-weight: bold; color: #6d28d9; letter-spacing: -0.3pt; }
        .doc-sub    { font-size: 8pt; color: #9ca3af; margin-top: 2pt; }
        .doc-number { font-size: 10pt; font-weight: bold; color: #374151; }
        .doc-date   { font-size: 8pt; color: #6b7280; margin-top: 2pt; }

        /* Parties */
        .parties { display: table; width: 100%; margin-bottom: 14pt; border: 1pt solid #e5e7eb; border-radius: 4pt; }
        .party-box { display: table-cell; width: 50%; padding: 9pt 10pt; vertical-align: top; }
        .party-box + .party-box { border-left: 1pt solid #e5e7eb; }
        .party-label { font-size: 7pt; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 4pt; }
        .party-name  { font-size: 10pt; font-weight: bold; color: #111827; }
        .party-line  { font-size: 8pt; color: #6b7280; margin-top: 1pt; }

        /* Section titles */
        .section-title { font-size: 8.5pt; font-weight: bold; color: #374151; margin: 12pt 0 6pt;
                         border-left: 3pt solid #7c3aed; padding-left: 6pt; }

        /* Detail grid */
        .detail-grid { display: table; width: 100%; margin-bottom: 10pt; }
        .detail-row  { display: table-row; }
        .detail-cell { display: table-cell; width: 25%; padding: 5pt 8pt; border: 0.5pt solid #e5e7eb; vertical-align: top; }
        .detail-label { font-size: 7pt; color: #9ca3af; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3pt; }
        .detail-value { font-size: 8.5pt; color: #111827; font-weight: bold; margin-top: 1pt; }

        /* Quantity table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
        th { background: #6d28d9; color: #fff; font-size: 8pt; padding: 5pt 8pt; text-align: left; font-weight: bold; }
        th.right { text-align: right; }
        td { font-size: 8.5pt; padding: 6pt 8pt; border-bottom: 0.5pt solid #f3f4f6; }
        td.right { text-align: right; }
        tr.total td { background: #f5f3ff; font-weight: bold; border-top: 1pt solid #7c3aed; }

        /* Quality */
        .quality-grid { display: table; width: 100%; margin-bottom: 10pt; }
        .quality-cell { display: table-cell; width: 20%; padding: 5pt 8pt; border: 0.5pt solid #e5e7eb; text-align: center; }
        .quality-label { font-size: 7pt; color: #9ca3af; }
        .quality-value { font-size: 9pt; font-weight: bold; color: #111827; margin-top: 1pt; }

        /* Status badge */
        .badge { display: inline-block; padding: 2pt 8pt; border-radius: 10pt; font-size: 7.5pt; font-weight: bold; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-zinc   { background: #f4f4f5; color: #52525b; }

        /* Notes */
        .notes-box { background: #fafafa; border: 0.5pt solid #e5e7eb; border-radius: 3pt; padding: 8pt; font-size: 8pt; color: #374151; }

        /* Signatures */
        .signatures { display: table; width: 100%; margin-top: 20pt; }
        .sig-cell { display: table-cell; width: 50%; padding: 0 15pt; vertical-align: bottom; text-align: center; }
        .sig-line { border-top: 1pt solid #374151; padding-top: 6pt; font-size: 7.5pt; color: #6b7280; }

        /* Footer */
        .footer { position: fixed; bottom: 0; left: 15mm; right: 15mm; font-size: 6.5pt; color: #9ca3af;
                  border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }

        .dispute-box { background: #fffbeb; border: 1pt solid #fde68a; border-radius: 3pt; padding: 8pt; margin-bottom: 10pt; }
        .dispute-title { font-size: 7.5pt; font-weight: bold; color: #92400e; margin-bottom: 3pt; }
        .dispute-text  { font-size: 8pt; color: #78350f; }
        .resolution-box { background: #eff6ff; border: 1pt solid #bfdbfe; border-radius: 3pt; padding: 8pt; margin-bottom: 10pt; }
        .resolution-title { font-size: 7.5pt; font-weight: bold; color: #1d4ed8; margin-bottom: 3pt; }
        .resolution-text  { font-size: 8pt; color: #1e40af; }
    </style>
</head>
<body>

@php
    $delivery  = $delivery;
    $planting  = $delivery->plotPlanting;
    $variety   = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
    $plot      = $planting?->plot?->name ?? '—';
    $vitic     = $delivery->viticulturist;
    $profile   = $vitic?->profile;
    $winery    = $delivery->harvest?->winery;
    $statusBadge = match($delivery->status) {
        'matched'  => ['class' => 'badge-green', 'label' => 'Confirmada por bodega'],
        'resolved' => ['class' => 'badge-blue',  'label' => 'Disputa resuelta'],
        'disputed' => ['class' => 'badge-amber', 'label' => 'Con diferencia'],
        default    => ['class' => 'badge-zinc',  'label' => 'Pendiente de confirmar'],
    };
@endphp

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="doc-title">Albarán de Entrega</div>
            <div class="doc-sub">Declaración de entrega de uva · Vendimia {{ $delivery->vintage_year }}</div>
        </div>
        <div class="header-right">
            @if($delivery->ticket_number)
                <div class="doc-number">Ticket: {{ $delivery->ticket_number }}</div>
            @endif
            <div class="doc-date">
                Fecha: {{ $delivery->delivery_date?->format('d/m/Y') ?? '—' }}
                @if($delivery->harvest_time) · {{ substr($delivery->harvest_time, 0, 5) }}h @endif
            </div>
            <div style="margin-top: 4pt;">
                <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
            </div>
        </div>
    </div>

    {{-- Partes --}}
    <div class="parties">
        <div class="party-box">
            <div class="party-label">Viticultor / Remitente</div>
            <div class="party-name">{{ $vitic?->name ?? '—' }}</div>
            @if($profile?->nif)
                <div class="party-line">NIF: {{ $profile->nif }}</div>
            @endif
            @if($profile?->address)
                <div class="party-line">{{ $profile->address }}</div>
            @endif
            @if($profile?->municipality?->name ?? $profile?->province?->name)
                <div class="party-line">
                    {{ $profile->municipality?->name }}@if($profile->municipality?->name && $profile->province?->name), @endif{{ $profile->province?->name }}
                </div>
            @endif
            @if($vitic?->email)
                <div class="party-line">{{ $vitic->email }}</div>
            @endif
        </div>
        <div class="party-box">
            <div class="party-label">Comprador / Destinatario</div>
            <div class="party-name">{{ $delivery->buyer_name }}</div>
            @if($winery)
                <div class="party-line" style="color:#7c3aed;">{{ __('Bodega registrada en Agro365') }}</div>
            @endif
            @if($delivery->destination_rega_code)
                <div class="party-line">REGA: {{ $delivery->destination_rega_code }}</div>
            @endif
        </div>
    </div>

    {{-- Detalle de la entrega --}}
    <div class="section-title">Detalle de la entrega</div>
    <div class="detail-grid">
        <div class="detail-row">
            <div class="detail-cell">
                <div class="detail-label">Variedad</div>
                <div class="detail-value">{{ $variety }}</div>
            </div>
            <div class="detail-cell">
                <div class="detail-label">{{ __('Parcela') }}</div>
                <div class="detail-value">{{ $plot }}</div>
            </div>
            <div class="detail-cell">
                <div class="detail-label">Añada</div>
                <div class="detail-value">{{ $delivery->vintage_year }}</div>
            </div>
            <div class="detail-cell">
                <div class="detail-label">Matrícula</div>
                <div class="detail-value">{{ $delivery->vehicle_plate ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Tabla de kilos --}}
    <div class="section-title">Cantidades</div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Concepto') }}</th>
                <th class="right">{{ __('Kg entregados') }}</th>
                <th class="right">{{ __('Precio / kg') }}</th>
                <th class="right">{{ __('Importe total') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $variety }} · {{ $plot }} · {{ $delivery->vintage_year }}</td>
                <td class="right">{{ number_format($delivery->delivered_kg, 0) }} kg</td>
                <td class="right">{{ $delivery->price_per_kg ? number_format($delivery->price_per_kg, 3) . ' €' : '—' }}</td>
                <td class="right">{{ $delivery->total_price ? number_format($delivery->total_price, 3) . ' €' : '—' }}</td>
            </tr>
            @if($delivery->harvest && abs((float)$delivery->delivered_kg - (float)$delivery->harvest->total_weight) > 0)
                <tr>
                    <td style="color:#6b7280; font-size:7.5pt;">{{ __('Kg confirmados por bodega') }}</td>
                    <td class="right" style="color:#1d4ed8; font-size:7.5pt;">{{ number_format($delivery->harvest->total_weight, 0) }} kg</td>
                    <td class="right">—</td>
                    <td class="right">—</td>
                </tr>
            @endif
            @if($delivery->total_price)
                <tr class="total">
                    <td colspan="3">{{ __('TOTAL') }}</td>
                    <td class="right">{{ number_format($delivery->total_price, 3) }} €</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Parámetros de calidad --}}
    @if($delivery->baume_degree || $delivery->brix_degree || $delivery->potential_alcohol || $delivery->acidity_level || $delivery->ph_level)
        <div class="section-title">Parámetros de calidad</div>
        <div class="quality-grid">
            @if($delivery->potential_alcohol)
                <div class="quality-cell">
                    <div class="quality-label">Alcohol pot.</div>
                    <div class="quality-value">{{ $delivery->potential_alcohol }}%</div>
                </div>
            @endif
            @if($delivery->baume_degree)
                <div class="quality-cell">
                    <div class="quality-label">Baumé</div>
                    <div class="quality-value">{{ $delivery->baume_degree }} °Bé</div>
                </div>
            @endif
            @if($delivery->brix_degree)
                <div class="quality-cell">
                    <div class="quality-label">Brix</div>
                    <div class="quality-value">{{ $delivery->brix_degree }} °Bx</div>
                </div>
            @endif
            @if($delivery->acidity_level)
                <div class="quality-cell">
                    <div class="quality-label">Acidez</div>
                    <div class="quality-value">{{ $delivery->acidity_level }} g/L</div>
                </div>
            @endif
            @if($delivery->ph_level)
                <div class="quality-cell">
                    <div class="quality-label">pH</div>
                    <div class="quality-value">{{ $delivery->ph_level }}</div>
                </div>
            @endif
        </div>
    @endif

    {{-- Incidencias de disputa/resolución --}}
    @if($delivery->hasDisputeNote())
        <div class="dispute-box">
            <div class="dispute-title">
                Reclamación del viticultor · {{ $delivery->dispute_submitted_at->format('d/m/Y H:i') }}
            </div>
            <div class="dispute-text">{{ $delivery->dispute_note }}</div>
        </div>
    @endif

    @if($delivery->isResolved())
        <div class="resolution-box">
            <div class="resolution-title">
                Respuesta de la bodega · {{ $delivery->dispute_resolved_at->format('d/m/Y H:i') }}
            </div>
            <div class="resolution-text">{{ $delivery->dispute_resolution_note }}</div>
        </div>
    @endif

    {{-- Notas --}}
    @if($delivery->notes)
        <div class="section-title">Observaciones</div>
        <div class="notes-box">{{ $delivery->notes }}</div>
    @endif

    {{-- Firmas --}}
    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line">Firma del viticultor<br><strong>{{ $vitic?->name ?? '—' }}</strong></div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">Firma del comprador / bodega<br><strong>{{ $delivery->buyer_name }}</strong></div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">Agro365 · Cuaderno Digital de Campo · {{ now()->format('d/m/Y') }}</div>
        <div class="footer-right">Entrega {{ $delivery->ticket_number ? '#' . $delivery->ticket_number . ' · ' : '' }}Añada {{ $delivery->vintage_year }}</div>
    </div>

</body>
</html>
