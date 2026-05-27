<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Recepción #{{ $harvest->id }}</title>
    <style>
        @page { margin: 15mm 12mm 20mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9pt; color: #1f2937; line-height: 1.5; }

        .header { border-bottom: 2pt solid #16a34a; padding-bottom: 10pt; margin-bottom: 14pt; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .title { font-size: 13pt; font-weight: bold; color: #15803d; }
        .winery-name { font-size: 10pt; font-weight: bold; }

        .section { margin-bottom: 12pt; }
        .section-title { font-size: 9pt; font-weight: bold; color: #15803d; border-bottom: 0.5pt solid #d1fae5; padding-bottom: 3pt; margin-bottom: 6pt; }
        .grid { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10pt; }
        .field { margin-bottom: 6pt; }
        .field-label { font-size: 7.5pt; color: #6b7280; }
        .field-value { font-size: 9pt; font-weight: bold; color: #111827; }

        .highlight { background: #f0fdf4; border: 1pt solid #bbf7d0; padding: 6pt 8pt; border-radius: 4pt; margin-bottom: 10pt; }
        .highlight-value { font-size: 16pt; font-weight: bold; color: #15803d; }
        .highlight-label { font-size: 7.5pt; color: #6b7280; }

        .badge { display: inline-block; padding: 2pt 6pt; border-radius: 3pt; font-size: 7.5pt; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red   { background: #fee2e2; color: #991b1b; }
        .badge-zinc  { background: #f4f4f5; color: #52525b; }
        .badge-orange{ background: #fff7ed; color: #9a3412; }

        .footer { position: fixed; bottom: 0; left: 12mm; right: 12mm; font-size: 7pt; color: #9ca3af;
                  border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; text-align: center; }
    </style>
</head>
<body>
    @php
        $planting    = $harvest->plotPlanting;
        $isCancelled = $harvest->status === 'cancelled';
    @endphp

    <div class="header">
        <div class="header-left">
            <div class="title">Recepción de Uva #{{ $harvest->id }}</div>
            <div style="font-size:8.5pt;color:#6b7280;margin-top:2pt;">
                {{ $harvest->harvest_start_date?->format('d/m/Y') }}
                @if($harvest->harvest_time) · {{ $harvest->harvest_time }} @endif
                · Añada {{ $harvest->batch?->vintage_year ?? $harvest->vintage ?? '—' }}
            </div>
        </div>
        <div class="header-right">
            <div class="winery-name">{{ $wineryName }}</div>
            <div style="font-size:7.5pt;color:#6b7280;margin-top:2pt;">
                @if($isCancelled)
                    <span class="badge badge-zinc">{{ __('ANULADA') }}</span>
                @elseif($harvest->disqualified)
                    <span class="badge badge-red">{{ __('DESCARTADA') }}</span>
                @else
                    <span class="badge badge-green">{{ __('ACTIVA') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Kg destacado --}}
    <div class="highlight">
        <div class="grid">
            <div class="col">
                <div class="highlight-label">Kg recibidos</div>
                <div class="highlight-value">{{ number_format($harvest->total_weight, 0) }} kg</div>
            </div>
            @if($harvest->yield_per_hectare)
            <div class="col">
                <div class="highlight-label">Rendimiento real</div>
                <div class="highlight-value" style="font-size:13pt;">{{ number_format($harvest->yield_per_hectare, 0) }} kg/ha</div>
            </div>
            @endif
            @if($harvest->total_value)
            <div class="col">
                <div class="highlight-label">Valor total</div>
                <div class="highlight-value" style="font-size:13pt;">{{ number_format($harvest->total_value, 3) }} €</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Contexto --}}
    <div class="section">
        <div class="section-title">{{ __('Viticultor y Parcela') }}</div>
        <div class="grid">
            <div class="col">
                <div class="field"><div class="field-label">Viticultor</div><div class="field-value">{{ $harvest->batch?->viticulturist?->name ?? '—' }}</div></div>
                <div class="field"><div class="field-label">{{ __('Parcela') }}</div><div class="field-value">{{ $planting?->plot?->name ?? '—' }}</div></div>
            </div>
            <div class="col">
                <div class="field"><div class="field-label">Variedad</div><div class="field-value">{{ $planting?->grapeVariety?->name ?? '—' }}</div></div>
                @if($planting?->area_planted)
                <div class="field"><div class="field-label">Superficie</div><div class="field-value">{{ number_format($planting->area_planted, 2) }} ha</div></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Datos recepción --}}
    <div class="section">
        <div class="section-title">Datos de la Recepción</div>
        <div class="grid">
            <div class="col">
                @if($harvest->harvest_ticket_number)
                <div class="field"><div class="field-label">Ticket / albarán</div><div class="field-value">{{ $harvest->harvest_ticket_number }}</div></div>
                @endif
                @if($harvest->price_per_kg)
                <div class="field"><div class="field-label">Precio / kg</div><div class="field-value">{{ number_format($harvest->price_per_kg, 3) }} €</div></div>
                @endif
                @if($harvest->container)
                <div class="field"><div class="field-label">Depósito destino</div><div class="field-value">{{ $harvest->container->name }}</div></div>
                @endif
            </div>
            <div class="col">
                @if($harvest->vehicle_plate)
                <div class="field"><div class="field-label">Matrícula</div><div class="field-value">{{ $harvest->vehicle_plate }}</div></div>
                @endif
                @if($harvest->transport_document_number)
                <div class="field"><div class="field-label">Doc. transporte</div><div class="field-value">{{ $harvest->transport_document_number }}</div></div>
                @endif
                @if($harvest->destination_rega_code)
                <div class="field"><div class="field-label">Código REGA destino</div><div class="field-value">{{ $harvest->destination_rega_code }}</div></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Calidad --}}
    @if($harvest->potential_alcohol || $harvest->baume_degree || $harvest->brix_degree || $harvest->acidity_level || $harvest->ph_level)
    <div class="section">
        <div class="section-title">Parámetros de Calidad</div>
        <div class="grid">
            <div class="col">
                @if($harvest->potential_alcohol)
                <div class="field"><div class="field-label">Alcohol potencial</div><div class="field-value">{{ $harvest->potential_alcohol }}%</div></div>
                @endif
                @if($harvest->baume_degree)
                <div class="field"><div class="field-label">Grado Baumé</div><div class="field-value">{{ $harvest->baume_degree }} °Bé</div></div>
                @endif
                @if($harvest->brix_degree)
                <div class="field"><div class="field-label">Brix</div><div class="field-value">{{ $harvest->brix_degree }} °Bx</div></div>
                @endif
            </div>
            <div class="col">
                @if($harvest->acidity_level)
                <div class="field"><div class="field-label">Acidez total</div><div class="field-value">{{ $harvest->acidity_level }} g/L</div></div>
                @endif
                @if($harvest->ph_level)
                <div class="field"><div class="field-label">pH</div><div class="field-value">{{ $harvest->ph_level }}</div></div>
                @endif
                @if($harvest->health_status)
                @php $sl = ['sano'=>'Sano','daño_leve'=>'Daño leve','daño_moderado'=>'Daño moderado','daño_grave'=>'Daño grave']; @endphp
                <div class="field"><div class="field-label">Estado sanitario</div><div class="field-value">{{ $sl[$harvest->health_status] ?? $harvest->health_status }}</div></div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($harvest->notes)
    <div class="section">
        <div class="section-title">Observaciones</div>
        <p style="font-size:8.5pt;color:#374151;">{{ $harvest->notes }}</p>
    </div>
    @endif

    {{-- Firmas --}}
    <div style="display:table;width:100%;margin-top:30pt;">
        <div style="display:table-row;">
            <div style="display:table-cell;width:50%;padding-right:20pt;text-align:center;">
                <div style="border-top:0.5pt solid #374151;margin-top:40pt;padding-top:5pt;font-size:8pt;color:#6b7280;">
                    Firma del viticultor / Proveedor
                </div>
            </div>
            <div style="display:table-cell;width:50%;padding-left:20pt;text-align:center;">
                <div style="border-top:0.5pt solid #374151;margin-top:40pt;padding-top:5pt;font-size:8pt;color:#6b7280;">
                    Firma de la bodega / Receptor
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Agro365 · Recepción #{{ $harvest->id }} · Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
