<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>INFOVI {{ $campaign }}/{{ $campaign + 1 }}</title>
    <style>
        @page { margin: 14mm 12mm 18mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8.5pt; color: #1f2937; line-height: 1.4; }

        /* Header */
        .header { border-bottom: 2pt solid #1d4ed8; padding-bottom: 8pt; margin-bottom: 10pt; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .title    { font-size: 13pt; font-weight: bold; color: #1d4ed8; }
        .subtitle { font-size: 8pt; color: #6b7280; margin-top: 2pt; }
        .meta     { font-size: 8pt; color: #374151; margin-top: 3pt; }

        /* Identity bar */
        .identity { display: table; width: 100%; border: 1pt solid #bfdbfe; background: #eff6ff;
                    border-radius: 3pt; padding: 6pt; margin-bottom: 10pt; }
        .id-cell  { display: table-cell; padding: 0 8pt; vertical-align: middle; }
        .id-label { font-size: 6.5pt; color: #3b82f6; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3pt; }
        .id-value { font-size: 9pt; font-weight: bold; color: #1e3a8a; }

        /* Section */
        .section       { margin-bottom: 12pt; }
        .section-title { font-size: 9pt; font-weight: bold; color: #1d4ed8; margin-bottom: 4pt;
                         border-left: 3pt solid #1d4ed8; padding-left: 5pt; }

        /* Tables */
        table  { width: 100%; border-collapse: collapse; }
        th     { background: #1d4ed8; color: #fff; font-size: 7.5pt; padding: 4pt 5pt;
                 text-align: left; font-weight: bold; }
        th.r   { text-align: right; }
        td     { font-size: 7.5pt; padding: 3pt 5pt; border-bottom: 0.5pt solid #e5e7eb; vertical-align: top; }
        td.r   { text-align: right; font-variant-numeric: tabular-nums; }
        td.tot { font-weight: bold; background: #eff6ff; }
        tr:nth-child(even) td { background: #f8fafc; }
        tr:nth-child(even) td.tot { background: #dbeafe; }

        /* Producer badge */
        .badge       { display: inline-block; padding: 2pt 6pt; border-radius: 3pt; font-size: 7.5pt; font-weight: bold; }
        .badge-gran  { background: #fef3c7; color: #92400e; border: 0.5pt solid #fcd34d; }
        .badge-peq   { background: #dcfce7; color: #166534; border: 0.5pt solid #86efac; }

        /* Footer */
        .footer { position: fixed; bottom: 0; left: 12mm; right: 12mm; font-size: 6.5pt;
                  color: #9ca3af; border-top: 0.5pt solid #e5e7eb; padding-top: 3pt;
                  display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }

        .notice { font-size: 7pt; color: #6b7280; background: #f9fafb; border: 0.5pt solid #e5e7eb;
                  padding: 5pt 7pt; border-radius: 3pt; margin-top: 8pt; }
    </style>
</head>
<body>

{{-- Footer (fixed) --}}
<div class="footer">
    <div class="footer-left">INFOVI · Real Decreto 739/2015 · Generado con Agro365 · {{ now()->format('d/m/Y H:i') }}</div>
    <div class="footer-right">Campaña {{ $campaign }}/{{ $campaign + 1 }}</div>
</div>

{{-- Header --}}
<div class="header">
    <div class="header-left">
        <div class="title">INFOVI — Declaración de Mercados Vitivinícolas</div>
        <div class="subtitle">Sistema de Información de Mercados del Sector Vitivinícola · MAPA / AICA</div>
        <div class="meta">
            Campaña: <strong>{{ $campaign }}/{{ $campaign + 1 }}</strong>
            &nbsp;·&nbsp;
            Período: {{ \Carbon\Carbon::parse($campaignStart)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($campaignEnd)->format('d/m/Y') }}
        </div>
    </div>
    <div class="header-right">
        <div class="meta"><strong>{{ $user->name }}</strong></div>
        @if($org?->reovi_number)
            <div class="meta">REOVI: {{ $org->reovi_number }}</div>
        @endif
        @if($org?->nidpb)
            <div class="meta">NIDPB: {{ $org->nidpb }}</div>
        @endif
        <div style="margin-top:4pt;">
            <span class="badge {{ $threshold['is_large'] ? 'badge-gran' : 'badge-peq' }}">
                {{ $threshold['is_large'] ? 'Gran productor' : 'Pequeño productor' }}
                (media {{ number_format($threshold['avg_hl'], 1, ',', '.') }} HL)
            </span>
        </div>
    </div>
</div>

{{-- Cuadro 1 — Entradas --}}
<div class="section">
    <div class="section-title">Cuadro 1 — Entradas de materia prima</div>
    <table>
        <tr>
            <th>{{ __('Concepto') }}</th>
            <th class="r">{{ __('Kg recibidos') }}</th>
            <th class="r">{{ __('Equivalente HL (÷130)') }}</th>
        </tr>
        <tr>
            <td>Uva propia / recibida campaña {{ $campaign }}</td>
            <td class="r">{{ number_format($entradas['uva_propia_kg'], 0, ',', '.') }}</td>
            <td class="r">{{ number_format($entradas['uva_propia_hl'], 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="tot">{{ __('Total entradas') }}</td>
            <td class="r tot">{{ number_format($entradas['uva_propia_kg'], 0, ',', '.') }} kg</td>
            <td class="r tot">{{ number_format($entradas['uva_propia_hl'], 2, ',', '.') }} HL</td>
        </tr>
    </table>
</div>

{{-- Cuadro 2 — Producción --}}
<div class="section">
    <div class="section-title">Cuadro 2 — Producción de vino (excluye mosto)</div>
    @if(count($produccion) > 0)
        <table>
            <tr>
                <th>{{ __('Tipo de vino') }}</th>
                <th class="r">{{ __('Nº vinos') }}</th>
                <th class="r">{{ __('Hectolitros (HL)') }}</th>
            </tr>
            @php $totalProd = 0; @endphp
            @foreach($produccion as $row)
                @php $totalProd += $row->hl; @endphp
                <tr>
                    <td>{{ $wineLabels[$row->wine_type] ?? $row->wine_type }}</td>
                    <td class="r">{{ $row->cnt }}</td>
                    <td class="r">{{ number_format($row->hl, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="tot">{{ __('Total producción') }}</td>
                <td class="r tot">—</td>
                <td class="r tot">{{ number_format($totalProd, 2, ',', '.') }} HL</td>
            </tr>
        </table>
    @else
        <p style="font-size:7.5pt; color:#6b7280; padding:4pt 0;">{{ __('Sin datos de producción para esta campaña.') }}</p>
    @endif
</div>

{{-- Cuadro 3 — Existencias --}}
<div class="section">
    <div class="section-title">Cuadro 3 — Existencias a fin de campaña (excluye mosto)</div>
    @if(count($existencias) > 0)
        <table>
            <tr>
                <th>{{ __('Tipo de vino') }}</th>
                <th class="r">{{ __('Nº referencias') }}</th>
                <th class="r">{{ __('Hectolitros (HL)') }}</th>
            </tr>
            @php $totalExist = 0; @endphp
            @foreach($existencias as $row)
                @php $totalExist += $row->hl; @endphp
                <tr>
                    <td>{{ $wineLabels[$row->wine_type] ?? $row->wine_type }}</td>
                    <td class="r">{{ $row->cnt }}</td>
                    <td class="r">{{ number_format($row->hl, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="tot">{{ __('Total existencias') }}</td>
                <td class="r tot">—</td>
                <td class="r tot">{{ number_format($totalExist, 2, ',', '.') }} HL</td>
            </tr>
        </table>
    @else
        <p style="font-size:7.5pt; color:#6b7280; padding:4pt 0;">{{ __('Sin instantánea de existencias. Registra una instantánea en SILICIE.') }}</p>
    @endif
</div>

{{-- Cuadro 4 — Ventas --}}
<div class="section">
    <div class="section-title">Cuadro 4 — Ventas (excluye mosto)</div>
    @if(count($ventas) > 0)
        <table>
            <tr>
                <th>{{ __('Tipo de vino') }}</th>
                <th class="r">{{ __('Botellas') }}</th>
                <th class="r">{{ __('Hectolitros (HL)') }}</th>
            </tr>
            @php $totalVent = 0; @endphp
            @foreach($ventas as $row)
                @php $totalVent += $row->hl; @endphp
                <tr>
                    <td>{{ $wineLabels[$row->wine_type] ?? $row->wine_type }}</td>
                    <td class="r">{{ number_format($row->bottles, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($row->hl, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="tot">{{ __('Total ventas') }}</td>
                <td class="r tot">—</td>
                <td class="r tot">{{ number_format($totalVent, 2, ',', '.') }} HL</td>
            </tr>
        </table>
    @else
        <p style="font-size:7.5pt; color:#6b7280; padding:4pt 0;">{{ __('Sin ventas registradas en este período.') }}</p>
    @endif
</div>

{{-- Mosto (condicional) --}}
@if($mosto['has_data'])
<div class="section">
    <div class="section-title">Cuadro 5 — Mosto</div>
    <table>
        <tr>
            <th>{{ __('Concepto') }}</th>
            <th class="r">{{ __('Hectolitros (HL)') }}</th>
        </tr>
        <tr>
            <td>{{ __('Existencias de mosto') }}</td>
            <td class="r">{{ number_format($mosto['existencias'], 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>{{ __('Mosto producido') }}</td>
            <td class="r">{{ number_format($mosto['producido'], 2, ',', '.') }}</td>
        </tr>
    </table>
</div>
@endif

{{-- Notice --}}
<div class="notice">
    <strong>{{ __('Aviso:') }}</strong> Este documento es un resumen de los datos registrados en Agro365 para facilitar la cumplimentación
    de la declaración oficial INFOVI en <strong>{{ __('mapa.gob.es/infovi') }}</strong>. No sustituye a la declaración oficial.
    Real Decreto 739/2015, de 31 de julio (MAPA/AICA). Datos en hectolitros (HL).
</div>

</body>
</html>
