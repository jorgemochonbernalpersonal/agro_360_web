<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Recepciones de Uva</title>
    <style>
        @page { margin: 15mm 12mm 20mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8.5pt; color: #1f2937; line-height: 1.4; }

        .header { border-bottom: 2pt solid #16a34a; padding-bottom: 10pt; margin-bottom: 12pt; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .title { font-size: 14pt; font-weight: bold; color: #15803d; }
        .subtitle { font-size: 9pt; color: #6b7280; margin-top: 2pt; }
        .winery-name { font-size: 10pt; font-weight: bold; color: #1f2937; }

        .stats { display: table; width: 100%; margin-bottom: 12pt; }
        .stat-box { display: table-cell; width: 25%; border: 1pt solid #e5e7eb; padding: 6pt; text-align: center; }
        .stat-value { font-size: 13pt; font-weight: bold; color: #15803d; }
        .stat-label { font-size: 7pt; color: #6b7280; margin-top: 2pt; }

        table { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        th { background: #15803d; color: #fff; font-size: 7.5pt; padding: 4pt 5pt; text-align: left; font-weight: bold; }
        td { font-size: 7.5pt; padding: 3.5pt 5pt; border-bottom: 0.5pt solid #f3f4f6; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        tr.cancelled td { color: #9ca3af; }
        tr.disqualified td { background: #fff7ed; }

        .badge { display: inline-block; padding: 1pt 4pt; border-radius: 3pt; font-size: 6.5pt; font-weight: bold; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-zinc   { background: #f4f4f5; color: #52525b; }

        .footer { position: fixed; bottom: 0; left: 12mm; right: 12mm; font-size: 7pt; color: #9ca3af;
                  border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; text-align: center; }
        .section-title { font-size: 9pt; font-weight: bold; color: #374151; margin: 10pt 0 4pt; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="title">Registro de Recepción de Uva</div>
            <div class="subtitle">
                @if($campaignYear) Añada {{ $campaignYear }} · @endif
                Generado el {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="header-right">
            <div class="winery-name">{{ $wineryName }}</div>
            <div style="font-size:8pt;color:#6b7280;margin-top:2pt;">Agro365</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ number_format($stats['total_kg'], 0) }} kg</div>
            <div class="stat-label">Total recibido</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['total_count'] }}</div>
            <div class="stat-label">Recepciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['viticulturists'] }}</div>
            <div class="stat-label">Viticultores</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($stats['disqualified_kg'], 0) }} kg</div>
            <div class="stat-label">Kg descartados</div>
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="section-title">Detalle de recepciones</div>
    <table>
        <thead>
            <tr>
                <th>Viticultor</th>
                <th>Parcela / Variedad</th>
                <th>Fecha</th>
                <th>Ticket</th>
                <th>Kg recibidos</th>
                <th>Kg/ha</th>
                <th>Alc. %</th>
                <th>Baumé</th>
                <th>Depósito</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($harvests as $harvest)
                @php
                    $isCancelled = $harvest->status === 'cancelled';
                    $rowClass = $isCancelled ? 'cancelled' : ($harvest->disqualified ? 'disqualified' : '');
                    $planting = $harvest->plotPlanting;
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $harvest->activity?->viticulturist?->name ?? '—' }}</td>
                    <td>
                        {{ $planting?->plot?->name ?? '—' }}<br>
                        <span style="color:#6b7280;">{{ $planting?->grapeVariety?->name ?? '—' }}</span>
                    </td>
                    <td>{{ $harvest->harvest_start_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $harvest->harvest_ticket_number ?? '—' }}</td>
                    <td style="font-weight:bold;">{{ number_format($harvest->total_weight, 0) }}</td>
                    <td>{{ $harvest->yield_per_hectare ? number_format($harvest->yield_per_hectare, 0) : '—' }}</td>
                    <td>{{ $harvest->potential_alcohol ? $harvest->potential_alcohol . '%' : '—' }}</td>
                    <td>{{ $harvest->baume_degree ? $harvest->baume_degree . '°' : '—' }}</td>
                    <td>{{ $harvest->container?->name ?? '—' }}</td>
                    <td>
                        @if($isCancelled)
                            <span class="badge badge-zinc">Anulada</span>
                        @elseif($harvest->disqualified)
                            <span class="badge badge-red">Descartada</span>
                        @else
                            <span class="badge badge-green">Activa</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center;color:#9ca3af;padding:12pt;">Sin recepciones</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Agro365 · Registro de recepción de uva · {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
