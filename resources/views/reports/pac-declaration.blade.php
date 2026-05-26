<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Declaración PAC {{ $declaration->year }}</title>
    <style>
        @page { margin: 15mm 12mm 20mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9pt; color: #1f2937; line-height: 1.4; }

        .header { border-bottom: 2pt solid #16a34a; padding-bottom: 10pt; margin-bottom: 14pt; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .title  { font-size: 15pt; font-weight: bold; color: #15803d; }
        .subtitle { font-size: 9pt; color: #6b7280; margin-top: 2pt; }
        .vitic-name { font-size: 10pt; font-weight: bold; }

        .meta-table { display: table; width: 100%; margin-bottom: 14pt; border: 1pt solid #e5e7eb; border-radius: 4pt; }
        .meta-row   { display: table-row; }
        .meta-cell  { display: table-cell; padding: 5pt 8pt; width: 25%; vertical-align: top; }
        .meta-label { font-size: 7pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.03em; }
        .meta-value { font-size: 9pt; font-weight: bold; color: #111827; margin-top: 1pt; }

        .stats { display: table; width: 100%; margin-bottom: 14pt; }
        .stat-box { display: table-cell; width: 25%; border: 1pt solid #e5e7eb; padding: 7pt; text-align: center; }
        .stat-value { font-size: 13pt; font-weight: bold; color: #15803d; }
        .stat-label { font-size: 7pt; color: #6b7280; margin-top: 2pt; }

        h2 { font-size: 10pt; font-weight: bold; color: #15803d; border-bottom: 1pt solid #dcfce7; padding-bottom: 3pt; margin: 12pt 0 8pt; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #15803d; color: #fff; font-size: 7.5pt; padding: 4pt 5pt; text-align: left; font-weight: bold; }
        td { font-size: 8pt; padding: 4pt 5pt; border-bottom: 0.5pt solid #f3f4f6; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        tfoot td { background: #f0fdf4; font-weight: bold; border-top: 1.5pt solid #16a34a; }

        .badge { display: inline-block; padding: 1pt 4pt; border-radius: 3pt; font-size: 6.5pt; font-weight: bold; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }

        .eco-list { font-size: 7pt; color: #047857; }

        .footer { position: fixed; bottom: 5mm; left: 12mm; right: 12mm; font-size: 7pt; color: #9ca3af;
            border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; display: table; width: 100%; }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }

        .signature-area { margin-top: 30pt; display: table; width: 100%; }
        .sig-box { display: table-cell; width: 45%; text-align: center; }
        .sig-line { border-top: 1pt solid #374151; margin: 0 10pt; padding-top: 4pt; font-size: 7.5pt; color: #6b7280; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <div class="title">Solicitud Única PAC {{ $declaration->year }}</div>
        <div class="subtitle">Declaración de Superficies Agrarias — Política Agrícola Común</div>
    </div>
    <div class="header-right">
        <div class="vitic-name">{{ $viticulturist->name }}</div>
        <div style="font-size:8pt; color:#6b7280; margin-top:2pt;">Generado el {{ now()->format('d/m/Y H:i') }}</div>
        @php
            $statusColors = ['draft'=>'amber','submitted'=>'blue','approved'=>'green','rejected'=>'red'];
            $statusLabels = ['draft'=>'Borrador','submitted'=>'Presentada','approved'=>'Aprobada','rejected'=>'Rechazada'];
        @endphp
        <div style="margin-top:4pt;">
            <span class="badge badge-{{ $statusColors[$declaration->status] ?? 'amber' }}">
                {{ $statusLabels[$declaration->status] ?? $declaration->status }}
            </span>
        </div>
    </div>
</div>

{{-- Metadatos --}}
<div class="meta-table">
    <div class="meta-row">
        <div class="meta-cell">
            <div class="meta-label">Titular</div>
            <div class="meta-value">{{ $viticulturist->name }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Año campaña</div>
            <div class="meta-value">{{ $declaration->year }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Referencia FEGA</div>
            <div class="meta-value">{{ $declaration->reference_number ?: '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Fecha presentación</div>
            <div class="meta-value">{{ $declaration->submitted_at?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats">
    <div class="stat-box">
        <div class="stat-value">{{ $declaration->items->count() }}</div>
        <div class="stat-label">Parcelas declaradas</div>
    </div>
    <div class="stat-box">
        <div class="stat-value">{{ number_format($declaration->total_declared_area, 2) }} ha</div>
        <div class="stat-label">Superficie total declarada</div>
    </div>
    <div class="stat-box">
        <div class="stat-value">{{ number_format($declaration->total_eligible_area, 2) }} ha</div>
        <div class="stat-label">Superficie admisible PAC</div>
    </div>
    <div class="stat-box">
        @php
            $coefGlobal = $declaration->total_declared_area > 0
                ? round($declaration->total_eligible_area / $declaration->total_declared_area, 4)
                : 0;
        @endphp
        <div class="stat-value">{{ number_format($coefGlobal, 4) }}</div>
        <div class="stat-label">Coeficiente admisibilidad</div>
    </div>
</div>

{{-- Tabla parcelas --}}
<h2>{{ __('Detalle de Parcelas') }}</h2>
<table>
    <thead>
        <tr>
            <th>{{ __('Parcela') }}</th>
            <th>{{ __('Municipio') }}</th>
            <th style="text-align:right;">{{ __('Sup. declarada') }}</th>
            <th style="text-align:right;">{{ __('Sup. admisible') }}</th>
            <th style="text-align:right;">{{ __('Coef.') }}</th>
            <th>{{ __('Eco-regímenes') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($declaration->items as $item)
            @php
                $coef = $item->declared_area > 0
                    ? round((float)$item->eligible_area / (float)$item->declared_area, 4)
                    : 0;
            @endphp
            <tr>
                <td><strong>{{ $item->plot?->name ?? '—' }}</strong></td>
                <td>{{ $item->plot?->municipality?->name ?? '—' }}</td>
                <td style="text-align:right; font-family:monospace;">{{ number_format($item->declared_area, 3) }} ha</td>
                <td style="text-align:right; font-family:monospace; color:#15803d;">{{ number_format($item->eligible_area, 3) }} ha</td>
                <td style="text-align:right; font-family:monospace;">{{ number_format($coef, 4) }}</td>
                <td>
                    @if(!empty($item->eco_schemes))
                        <div class="eco-list">{{ implode(', ', array_map(fn($k) => $ecoSchemesMap[$k] ?? $k, $item->eco_schemes)) }}</div>
                    @else
                        <span style="color:#9ca3af;">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong>{{ __('TOTAL') }}</strong></td>
            <td style="text-align:right; font-family:monospace;">{{ number_format($declaration->total_declared_area, 3) }} ha</td>
            <td style="text-align:right; font-family:monospace; color:#15803d;">{{ number_format($declaration->total_eligible_area, 3) }} ha</td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>

@if($declaration->notes)
    <h2 style="margin-top:14pt;">{{ __('Notas') }}</h2>
    <p style="font-size:8.5pt; color:#374151;">{{ $declaration->notes }}</p>
@endif

{{-- Firmas --}}
<div class="signature-area" style="margin-top:40pt;">
    <div class="sig-box">
        <div class="sig-line">Firma del titular</div>
    </div>
    <div class="sig-box" style="text-align:center; vertical-align:bottom;">
        <div style="font-size:7pt; color:#9ca3af; margin-bottom:6pt;">Sello organismo pagador</div>
        <div class="sig-line">Fecha y firma de recepción</div>
    </div>
</div>

<div class="footer">
    <div class="footer-left">Agro365 — Declaración PAC {{ $declaration->year }} · {{ $viticulturist->name }}</div>
    <div class="footer-right">Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</div>

</body>
</html>
