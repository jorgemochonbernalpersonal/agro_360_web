<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Análisis de Calidad — Vendimia{{ $campaignYear ? ' ' . $campaignYear : '' }}</title>
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

        .stats { display: table; width: 100%; margin-bottom: 14pt; }
        .stat-box { display: table-cell; border: 1pt solid #e5e7eb; padding: 7pt 8pt; text-align: center; }
        .stat-value { font-size: 14pt; font-weight: bold; color: #15803d; }
        .stat-value.blue   { color: #1d4ed8; }
        .stat-value.violet { color: #7c3aed; }
        .stat-value.amber  { color: #b45309; }
        .stat-label { font-size: 7pt; color: #6b7280; margin-top: 2pt; }

        .section-title { font-size: 10pt; font-weight: bold; color: #15803d; margin: 12pt 0 5pt; border-left: 3pt solid #16a34a; padding-left: 6pt; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #15803d; color: #fff; font-size: 7.5pt; padding: 4pt 5pt; text-align: left; font-weight: bold; }
        th.right { text-align: right; }
        td { font-size: 7.5pt; padding: 3.5pt 5pt; border-bottom: 0.5pt solid #f3f4f6; vertical-align: middle; }
        td.right { text-align: right; }
        td.bold  { font-weight: bold; }
        td.green { color: #15803d; font-weight: bold; }
        td.blue  { color: #1d4ed8; font-weight: bold; }
        td.amber { color: #b45309; font-weight: bold; }
        td.muted { color: #9ca3af; }
        tr:nth-child(even) td { background: #f9fafb; }

        .health-badge { display: inline-block; padding: 1pt 4pt; border-radius: 3pt; font-size: 6pt; font-weight: bold; margin: 1pt; }
        .health-sano     { background: #dcfce7; color: #166534; }
        .health-leve     { background: #fef3c7; color: #92400e; }
        .health-moderado { background: #fed7aa; color: #9a3412; }
        .health-grave    { background: #fee2e2; color: #991b1b; }

        .footer { position: fixed; bottom: 0; left: 12mm; right: 12mm; font-size: 7pt; color: #9ca3af;
                  border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <div class="title">Análisis de Calidad — Vendimia{{ $campaignYear ? ' ' . $campaignYear : '' }}</div>
            <div class="subtitle">Generado el {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="header-right">
            <div class="winery-name">{{ $wineryName }}</div>
            <div style="font-size:8pt;color:#6b7280;margin-top:2pt;">Agro365</div>
        </div>
    </div>

    {{-- Stats globales --}}
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ number_format($globalStats['total_kg'], 0) }} kg</div>
            <div class="stat-label">Total recibido</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $globalStats['total_entries'] }}</div>
            <div class="stat-label">Recepciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-value {{ $globalStats['avg_alcohol'] ? 'blue' : 'muted' }}">
                {{ $globalStats['avg_alcohol'] !== null ? $globalStats['avg_alcohol'] . '%' : '—' }}
            </div>
            <div class="stat-label">Media alcohol pot.</div>
        </div>
        <div class="stat-box">
            <div class="stat-value {{ $globalStats['avg_baume'] ? 'violet' : 'muted' }}">
                {{ $globalStats['avg_baume'] !== null ? $globalStats['avg_baume'] . ' °Bé' : '—' }}
            </div>
            <div class="stat-label">Media Baumé</div>
        </div>
        <div class="stat-box">
            <div class="stat-value {{ $globalStats['avg_brix'] ? '' : 'muted' }}">
                {{ $globalStats['avg_brix'] !== null ? $globalStats['avg_brix'] . ' °Bx' : '—' }}
            </div>
            <div class="stat-label">Media Brix</div>
        </div>
        <div class="stat-box">
            <div class="stat-value {{ $globalStats['avg_acidity'] ? 'amber' : 'muted' }}">
                {{ $globalStats['avg_acidity'] !== null ? $globalStats['avg_acidity'] . ' g/L' : '—' }}
            </div>
            <div class="stat-label">Media acidez</div>
        </div>
        <div class="stat-box">
            <div class="stat-value {{ $globalStats['avg_ph'] ? '' : 'muted' }}">
                {{ $globalStats['avg_ph'] ?? '—' }}
            </div>
            <div class="stat-label">Media pH</div>
        </div>
    </div>

    {{-- Por viticultor --}}
    @if($byViticulturist->isNotEmpty())
        <div class="section-title">Por viticultor</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Viticultor') }}</th>
                    <th class="right">{{ __('Total kg') }}</th>
                    <th class="right">{{ __('Recepciones') }}</th>
                    <th class="right">{{ __('Alc. pot. %') }}</th>
                    <th class="right">{{ __('Baumé °Bé') }}</th>
                    <th class="right">{{ __('Brix °Bx') }}</th>
                    <th class="right">{{ __('Acidez g/L') }}</th>
                    <th class="right">pH</th>
                    <th>{{ __('Estado sanitario') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byViticulturist as $item)
                    <tr>
                        <td class="bold">{{ $item['label'] }}</td>
                        <td class="right green">{{ number_format($item['total_kg'], 0) }}</td>
                        <td class="right">{{ $item['count'] }}</td>
                        <td class="right {{ $item['avg_alcohol'] ? 'blue' : 'muted' }}">{{ $item['avg_alcohol'] ?? '—' }}</td>
                        <td class="right {{ $item['avg_baume'] ? '' : 'muted' }}">{{ $item['avg_baume'] ?? '—' }}</td>
                        <td class="right {{ $item['avg_brix'] ? '' : 'muted' }}">{{ $item['avg_brix'] ?? '—' }}</td>
                        <td class="right {{ $item['avg_acidity'] ? 'amber' : 'muted' }}">{{ $item['avg_acidity'] ?? '—' }}</td>
                        <td class="right {{ $item['avg_ph'] ? '' : 'muted' }}">{{ $item['avg_ph'] ?? '—' }}</td>
                        <td>
                            @foreach($item['health_dist'] as $status => $count)
                                @php
                                    $cls = match($status) {
                                        'sano'           => 'health-sano',
                                        'daño_leve'      => 'health-leve',
                                        'daño_moderado'  => 'health-moderado',
                                        'daño_grave'     => 'health-grave',
                                        default          => 'health-sano',
                                    };
                                    $lbl = match($status) {
                                        'sano'           => 'Sano',
                                        'daño_leve'      => 'D. Leve',
                                        'daño_moderado'  => 'D. Mod.',
                                        'daño_grave'     => 'D. Grave',
                                        default          => $status,
                                    };
                                @endphp
                                <span class="health-badge {{ $cls }}">{{ $lbl }} ({{ $count }})</span>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Por variedad --}}
    @if($byVariety->isNotEmpty())
        <div class="section-title" style="margin-top:16pt;">Por variedad</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Variedad') }}</th>
                    <th class="right">{{ __('Total kg') }}</th>
                    <th class="right">{{ __('Entradas') }}</th>
                    <th class="right">{{ __('Alc. pot. %') }}</th>
                    <th class="right">{{ __('Baumé °Bé') }}</th>
                    <th class="right">{{ __('Brix °Bx') }}</th>
                    <th class="right">{{ __('Acidez g/L') }}</th>
                    <th class="right">pH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byVariety as $item)
                    <tr>
                        <td class="bold">{{ $item['label'] }}</td>
                        <td class="right green">{{ number_format($item['total_kg'], 0) }}</td>
                        <td class="right">{{ $item['count'] }}</td>
                        <td class="right {{ $item['avg_alcohol'] ? 'blue' : 'muted' }}">{{ $item['avg_alcohol'] !== null ? $item['avg_alcohol'] . '%' : '—' }}</td>
                        <td class="right {{ $item['avg_baume'] ? '' : 'muted' }}">{{ $item['avg_baume'] !== null ? $item['avg_baume'] . '°' : '—' }}</td>
                        <td class="right {{ $item['avg_brix'] ? '' : 'muted' }}">{{ $item['avg_brix'] !== null ? $item['avg_brix'] . '°' : '—' }}</td>
                        <td class="right {{ $item['avg_acidity'] ? 'amber' : 'muted' }}">{{ $item['avg_acidity'] !== null ? $item['avg_acidity'] . ' g/L' : '—' }}</td>
                        <td class="right {{ $item['avg_ph'] ? '' : 'muted' }}">{{ $item['avg_ph'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Análisis de Calidad — {{ $wineryName }} · Agro365 · Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
