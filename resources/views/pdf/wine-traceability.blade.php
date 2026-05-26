<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trazabilidad — {{ $wine->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }

        .page-header { background: #2d6a2d; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .page-header h1 { font-size: 18px; font-weight: bold; }
        .page-header .subtitle { font-size: 11px; opacity: 0.85; margin-top: 2px; }
        .page-header .meta { display: flex; gap: 20px; margin-top: 10px; font-size: 9px; opacity: 0.75; }

        .section { margin-bottom: 18px; }
        .section-title {
            font-size: 11px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.5px; color: #2d6a2d;
            border-bottom: 1.5px solid #2d6a2d; padding-bottom: 4px; margin-bottom: 10px;
        }

        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #f4f8f4; text-align: left; padding: 5px 8px; font-weight: bold; color: #444; border-bottom: 1px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 10px;
            font-size: 8px; font-weight: bold;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-gray   { background: #f3f4f6; color: #374151; }

        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; }
        .info-box .label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-box .value { font-size: 13px; font-weight: bold; color: #111; margin-top: 1px; }
        .info-box .sub   { font-size: 8px; color: #6b7280; margin-top: 1px; }

        .trace-token { font-size: 8px; color: #9ca3af; font-family: monospace; }
        .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 8px; color: #9ca3af; display: flex; justify-content: space-between; }

        .alert-row td { background: #fef2f2; }
        .ok-row td { background: #f0fdf4; }

        .no-data { font-style: italic; color: #9ca3af; padding: 8px 0; font-size: 9px; }
    </style>
</head>
<body>

    {{-- Cabecera --}}
    <div class="page-header">
        <h1>{{ $wine->name }}
            @if($wine->vintage) — Añada {{ $wine->vintage }} @endif
        </h1>
        <div class="subtitle">
            Informe de Trazabilidad
            @if($wine->internal_code) · Ref. {{ $wine->internal_code }} @endif
        </div>
        <div class="meta">
            <span>Tipo: {{ $wine->type_label }}</span>
            @if($wine->aging_type_label)<span>Crianza: {{ $wine->aging_type_label }}</span>@endif
            @if($wine->category_label)<span>Categoría: {{ $wine->category_label }}</span>@endif
            @if($wine->volume_liters)<span>Volumen: {{ number_format($wine->volume_liters, 0) }} L</span>@endif
            @if($wine->oenologist)<span>Enólogo/a: {{ $wine->oenologist->name }}</span>@endif
            <span>Generado: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- Resumen estado --}}
    <div class="section">
        <div class="info-grid">
            <div class="info-box">
                <div class="label">Estado</div>
                <div class="value" style="font-size:11px;">{{ $wine->status_label }}</div>
            </div>
            <div class="info-box">
                <div class="label">Orígenes (cosechas)</div>
                <div class="value">{{ $wine->wineHarvests->count() }}</div>
            </div>
            <div class="info-box">
                <div class="label">Etapas de proceso</div>
                <div class="value">{{ $wine->processDetails->count() }}</div>
            </div>
            <div class="info-box">
                <div class="label">Análisis realizados</div>
                <div class="value">{{ $wine->analyses->count() }}</div>
            </div>
            <div class="info-box">
                <div class="label">Lotes embotellados</div>
                <div class="value">{{ $wine->bottlings->count() }}</div>
            </div>
            <div class="info-box">
                <div class="label">Lotes etiquetados</div>
                <div class="value">{{ $wine->labelings->count() }}</div>
            </div>
        </div>
        <div class="trace-token">Token de trazabilidad: {{ $wine->trace_token }}</div>
    </div>

    {{-- Orígenes --}}
    @if($wine->wineHarvests->count())
    <div class="section">
        <div class="section-title">Orígenes — Cosechas vinculadas</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Cosecha / Ref.') }}</th>
                    <th>{{ __('Cantidad (kg)') }}</th>
                    <th>{{ __('Porcentaje') }}</th>
                    <th>{{ __('Variedad') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wine->wineHarvests as $wh)
                <tr>
                    <td>{{ $wh->harvest?->reference ?? $wh->harvest_id }}</td>
                    <td>{{ $wh->quantity_kg ? number_format($wh->quantity_kg, 1).' kg' : '—' }}</td>
                    <td>{{ $wh->percentage ? $wh->percentage.'%' : '—' }}</td>
                    <td>{{ $wh->harvest?->variety ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Etapas de proceso --}}
    @if($wine->processDetails->count())
    <div class="section">
        <div class="section-title">Proceso de elaboración</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Etapa') }}</th>
                    <th>{{ __('Inicio') }}</th>
                    <th>{{ __('Fin') }}</th>
                    <th>{{ __('Duración') }}</th>
                    <th>{{ __('Observaciones') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wine->processDetails as $step)
                <tr>
                    <td><strong>{{ $step->process_type_label }}</strong></td>
                    <td>{{ $step->start_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $step->end_date?->format('d/m/Y') ?? 'En curso' }}</td>
                    <td>
                        @if($step->start_date && $step->end_date)
                            {{ $step->start_date->diffInDays($step->end_date) }} días
                        @elseif($step->start_date)
                            {{ $step->start_date->diffInDays(now()) }} días (activo)
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $step->observations ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Análisis --}}
    @if($wine->analyses->count())
    <div class="section">
        <div class="section-title">Análisis de laboratorio</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Fecha') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Grado (% vol)') }}</th>
                    <th>{{ __('SO₂ libre (mg/L)') }}</th>
                    <th>{{ __('SO₂ total (mg/L)') }}</th>
                    <th>{{ __('Acidez total (g/L)') }}</th>
                    <th>pH</th>
                    <th>{{ __('Resultado') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wine->analyses as $analysis)
                @php
                    $isLowSo2 = $analysis->isSo2FreeLow();
                @endphp
                <tr class="{{ $isLowSo2 ? 'alert-row' : '' }}">
                    <td>{{ $analysis->analysis_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $analysis->type_label }}</td>
                    <td>{{ $analysis->alcoholic_strength ?? $analysis->alcohol ?? '—' }}</td>
                    <td>{{ $analysis->free_so2 ?? $analysis->so2_free ?? '—' }}{{ ($analysis->free_so2 || $analysis->so2_free) && $isLowSo2 ? ' ⚠' : '' }}</td>
                    <td>{{ $analysis->total_so2 ?? $analysis->so2_total ?? '—' }}</td>
                    <td>{{ $analysis->total_acidity ?? '—' }}</td>
                    <td>{{ $analysis->ph ?? '—' }}</td>
                    <td>{{ $analysis->result_label ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Embotellados --}}
    @if($wine->bottlings->count())
    <div class="section">
        <div class="section-title">Embotellado</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Fecha') }}</th>
                    <th>{{ __('Formato') }}</th>
                    <th>{{ __('Botellas') }}</th>
                    <th>{{ __('Litros') }}</th>
                    <th>{{ __('Nº Lote') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wine->bottlings as $bottling)
                <tr>
                    <td>{{ $bottling->bottling_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $bottling->bottle_format ? $bottling->bottle_format.' mL' : '—' }}</td>
                    <td>{{ $bottling->quantity_bottles ? number_format($bottling->quantity_bottles) : '—' }}</td>
                    <td>{{ $bottling->quantity_liters ? number_format($bottling->quantity_liters, 1).' L' : '—' }}</td>
                    <td>{{ $bottling->lot_number ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Etiquetado --}}
    @if($wine->labelings->count())
    <div class="section">
        <div class="section-title">Etiquetado</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Fecha') }}</th>
                    <th>{{ __('Lote etiquetas') }}</th>
                    <th>{{ __('Cantidad') }}</th>
                    <th>{{ __('Rango') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wine->labelings as $labeling)
                <tr>
                    <td>{{ $labeling->labeling_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $labeling->labelBatch?->name ?? '—' }}</td>
                    <td>{{ $labeling->quantity_labeled ? number_format($labeling->quantity_labeled) : '—' }}</td>
                    <td>
                        @if($labeling->from_number && $labeling->to_number)
                            {{ number_format($labeling->from_number) }} – {{ number_format($labeling->to_number) }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <span>Agro365 · Informe de trazabilidad generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</span>
        <span>Token: {{ $wine->trace_token }}</span>
    </div>

</body>
</html>
