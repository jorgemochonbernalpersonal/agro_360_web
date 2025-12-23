<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe Oficial - Cuaderno Digital Completo</title>
    <style>
        @page {
            margin: 20mm 15mm;
            @bottom-right {
                content: counter(page);
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #2c5530 0%, #3a7040 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .header h1 {
            font-size: 16pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 10pt;
            opacity: 0.95;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .info-box h3 {
            color: #2c5530;
            font-size: 11pt;
            margin-bottom: 8px;
            border-bottom: 2px solid #2c5530;
            padding-bottom: 4px;
            font-weight: bold;
        }
        .info-grid {
            width: 100%;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 30%;
            color: #555;
        }
        .info-value {
            display: inline-block;
            width: 68%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7.5pt;
        }
        thead {
            background: #2c5530;
            color: white;
        }
        th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 7.5pt;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .signature-section {
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #2c5530;
            background: #f0f7f0;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        .signature-section h3 {
            color: #2c5530;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        .qr-container {
            text-align: center;
            margin: 15px 0;
        }
        .qr-container img {
            border: 3px solid #2c5530;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .official-stamp {
            color: #d32f2f;
            font-weight: bold;
            font-size: 12pt;
            text-align: center;
            border: 3px double #d32f2f;
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .hash-code {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 8pt;
        }
        small {
            font-size: 7pt;
            color: #666;
        }
        .activity-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .activity-section h4 {
            color: #2c5530;
            font-size: 10pt;
            margin: 15px 0 8px;
            padding: 8px;
            background: #e8f5e9;
            border-left: 4px solid #2c5530;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .stats-item {
            display: table-cell;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .stats-item strong {
            display: block;
            font-size: 14pt;
            color: #2c5530;
        }
        .stats-item span {
            font-size: 8pt;
            color: #666;
        }
        
        /* Marca de agua para informes invalidados */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.12);
            z-index: 9999;
            pointer-events: none;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            letter-spacing: 10px;
        }
    </style>
</head>
<body>
    {{-- Marca de agua si el informe está invalidado --}}
    @if(!$report->isValid())
        <div class="watermark">COPIA NO VÁLIDA</div>
    @endif
    {{-- Encabezado --}}
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" style="height: 45px; width: auto;" alt="Agro365 Logo">
            @endif
            <div style="flex: 1;">
                <h1>📔 CUADERNO DIGITAL COMPLETO</h1>
                <div class="subtitle">Informe Oficial - Campaña {{ $campaign->name }} ({{ $campaign->year }})</div>
            </div>
        </div>
    </div>

    {{-- Datos de la Explotación --}}
    <div class="info-box">
        <h3>DATOS DE LA EXPLOTACIÓN</h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Titular:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">NIF/CIF:</span>
                <span class="info-value">{{ $profile->nif ?? 'No especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Domicilio:</span>
                <span class="info-value">
                    {{ $profile->address ?? '' }}
                    @if($profile && $profile->municipality)
                        , {{ $profile->municipality->name }}
                    @endif
                    @if($profile && $profile->province)
                        ({{ $profile->province->name }})
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
        </div>
    </div>

    {{-- Información de la Campaña --}}
    <div class="info-box">
        <h3>INFORMACIÓN DE LA CAMPAÑA</h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Campaña:</span>
                <span class="info-value">{{ $campaign->name }} ({{ $campaign->year }})</span>
            </div>
            <div class="info-row">
                <span class="info-label">Periodo:</span>
                <span class="info-value">{{ $period_start->format('d/m/Y') }} - {{ $period_end->format('d/m/Y') }}</span>
            </div>
            @if($campaign->description)
            <div class="info-row">
                <span class="info-label">Descripción:</span>
                <span class="info-value">{{ $campaign->description }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Estadísticas Resumen --}}
    <div class="stats-grid">
        <div class="stats-item">
            <strong>{{ $stats['total_activities'] }}</strong>
            <span>Total Actividades</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['phytosanitary_count'] }}</strong>
            <span>Tratamientos</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['fertilization_count'] }}</strong>
            <span>Fertilizaciones</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['irrigation_count'] }}</strong>
            <span>Riegos</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['cultural_count'] }}</strong>
            <span>Labores</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['observation_count'] }}</strong>
            <span>Observaciones</span>
        </div>
        <div class="stats-item">
            <strong>{{ $stats['harvest_count'] }}</strong>
            <span>Cosechas</span>
        </div>
    </div>

    <div class="official-stamp">
        ✓ DOCUMENTO OFICIAL FIRMADO ELECTRÓNICAMENTE
    </div>

    {{-- Actividades por Tipo --}}
    
    @php
        $activitiesByType = $activities->groupBy('activity_type');
    @endphp

    {{-- Tratamientos Fitosanitarios --}}
    @if($activitiesByType->has('phytosanitary') && $activitiesByType->get('phytosanitary')->isNotEmpty())
    <div class="activity-section">
        <h4>🧪 TRATAMIENTOS FITOSANITARIOS</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 12%;">Parcela</th>
                    <th style="width: 15%;">Producto</th>
                    <th style="width: 8%;">Dosis/ha</th>
                    <th style="width: 7%;">Área</th>
                    <th style="width: 12%;">Plaga</th>
                    <th style="width: 12%;">Aplicador</th>
                    <th style="width: 10%;">Condiciones</th>
                    <th style="width: 7%;">Plazo</th>
                    <th style="width: 9%;">F. Segura</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('phytosanitary') as $activity)
                    @php
                        $phyto = $activity->phytosanitaryTreatment;
                        $product = $phyto->product ?? null;
                        $applicator = $activity->crewMember ?? $activity->crew;
                        $safeDate = $product && $product->withdrawal_period_days 
                            ? $activity->activity_date->copy()->addDays($product->withdrawal_period_days)
                            : null;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $activity->plot->name }}</strong>
                            @if($activity->plotPlanting)
                                <br><small class="text-gray-600">
                                    Plantación: {{ $activity->plotPlanting->name }}
                                    @if($activity->plotPlanting->grapeVariety)
                                        ({{ $activity->plotPlanting->grapeVariety->name }})
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td>{{ $product->name ?? 'N/A' }}</td>
                        <td>{{ $phyto->dose_per_hectare ?? '--' }} {{ $product->unit ?? 'L' }}</td>
                        <td>{{ $phyto->area_treated ? number_format($phyto->area_treated, 2) : '--' }} ha</td>
                        <td>{{ $phyto->target_pest ?? 'N/A' }}</td>
                        <td>
                            @if($applicator)
                                {{ $applicator->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ $activity->temperature ?? '--' }}°C<br>
                                {{ $phyto->wind_speed ?? '--' }} km/h<br>
                                H: {{ $phyto->humidity ?? '--' }}%
                            </small>
                        </td>
                        <td>{{ $product->withdrawal_period_days ?? 0 }} días</td>
                        <td>{{ $safeDate ? $safeDate->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Fertilizaciones --}}
    @if($activitiesByType->has('fertilization') && $activitiesByType->get('fertilization')->isNotEmpty())
    <div class="activity-section">
        <h4>🌱 FERTILIZACIONES</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 15%;">Parcela</th>
                    <th style="width: 20%;">Fertilizante</th>
                    <th style="width: 10%;">Cantidad</th>
                    <th style="width: 10%;">Método</th>
                    <th style="width: 15%;">Aplicador</th>
                    <th style="width: 20%;">Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('fertilization') as $activity)
                    @php
                        $fert = $activity->fertilization;
                        $applicator = $activity->crewMember ?? $activity->crew;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td><strong>{{ $activity->plot->name }}</strong></td>
                        <td>{{ $fert->fertilizer_name ?? 'N/A' }}</td>
                        <td>{{ $fert->quantity ?? '--' }} {{ $fert->unit ?? 'kg' }}</td>
                        <td>{{ $fert->application_method ?? 'N/A' }}</td>
                        <td>
                            @if($applicator)
                                {{ $applicator->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td><small>{{ $activity->notes ?? '--' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Riegos --}}
    @if($activitiesByType->has('irrigation') && $activitiesByType->get('irrigation')->isNotEmpty())
    <div class="activity-section">
        <h4>💧 RIEGOS</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 15%;">Parcela</th>
                    <th style="width: 12%;">Volumen</th>
                    <th style="width: 12%;">Duración</th>
                    <th style="width: 15%;">Método</th>
                    <th style="width: 15%;">Aplicador</th>
                    <th style="width: 21%;">Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('irrigation') as $activity)
                    @php
                        $irr = $activity->irrigation;
                        $applicator = $activity->crewMember ?? $activity->crew;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td><strong>{{ $activity->plot->name }}</strong></td>
                        <td>{{ $irr->water_volume ?? '--' }} L</td>
                        <td>{{ $irr->duration_minutes ?? '--' }} min</td>
                        <td>{{ $irr->irrigation_method ?? 'N/A' }}</td>
                        <td>
                            @if($applicator)
                                {{ $applicator->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td><small>{{ $activity->notes ?? '--' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Labores Culturales --}}
    @if($activitiesByType->has('cultural') && $activitiesByType->get('cultural')->isNotEmpty())
    <div class="activity-section">
        <h4>🔧 LABORES CULTURALES</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 15%;">Parcela</th>
                    <th style="width: 20%;">Tipo de Labor</th>
                    <th style="width: 15%;">Maquinaria</th>
                    <th style="width: 15%;">Aplicador</th>
                    <th style="width: 25%;">Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('cultural') as $activity)
                    @php
                        $cultural = $activity->culturalWork;
                        $applicator = $activity->crewMember ?? $activity->crew;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td><strong>{{ $activity->plot->name }}</strong></td>
                        <td>{{ $cultural->work_type ?? 'N/A' }}</td>
                        <td>
                            @if($activity->machinery)
                                {{ $activity->machinery->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($applicator)
                                {{ $applicator->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td><small>{{ $activity->notes ?? '--' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Observaciones --}}
    @if($activitiesByType->has('observation') && $activitiesByType->get('observation')->isNotEmpty())
    <div class="activity-section">
        <h4>👁️ OBSERVACIONES</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 15%;">Parcela</th>
                    <th style="width: 20%;">Tipo</th>
                    <th style="width: 15%;">Severidad</th>
                    <th style="width: 40%;">Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('observation') as $activity)
                    @php
                        $obs = $activity->observation;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td><strong>{{ $activity->plot->name }}</strong></td>
                        <td>{{ $obs->observation_type ?? 'N/A' }}</td>
                        <td>{{ $obs->severity ?? 'N/A' }}</td>
                        <td><small>{{ $obs->description ?? $activity->notes ?? '--' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Cosechas --}}
    @if($activitiesByType->has('harvest') && $activitiesByType->get('harvest')->isNotEmpty())
    <div class="activity-section">
        <h4>🍇 COSECHAS</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 12%;">Parcela</th>
                    <th style="width: 10%;">Peso Total</th>
                    <th style="width: 10%;">Rendimiento</th>
                    <th style="width: 8%;">Grado</th>
                    <th style="width: 10%;">Estado</th>
                    <th style="width: 12%;">Destino</th>
                    <th style="width: 15%;">Comprador</th>
                    <th style="width: 15%;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activitiesByType->get('harvest') as $activity)
                    @php
                        $harvest = $activity->harvest;
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date->format('d/m/Y') }}</td>
                        <td><strong>{{ $activity->plot->name }}</strong></td>
                        <td>{{ $harvest->total_weight ? number_format($harvest->total_weight, 2) : '--' }} kg</td>
                        <td>{{ $harvest->yield_per_hectare ? number_format($harvest->yield_per_hectare, 2) : '--' }} kg/ha</td>
                        <td>
                            @if($harvest->baume_degree)
                                {{ $harvest->baume_degree }}°Bé
                            @else
                                --
                            @endif
                        </td>
                        <td>{{ $harvest->health_status ?? 'N/A' }}</td>
                        <td>{{ $harvest->destination_type ?? 'N/A' }}</td>
                        <td><small>{{ $harvest->buyer_name ?? '--' }}</small></td>
                        <td>
                            @if($harvest->total_value)
                                {{ number_format($harvest->total_value, 2) }} €
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Sección de Firma y Verificación --}}
    <div class="signature-section">
        <h3>🔐 FIRMA ELECTRÓNICA Y VERIFICACIÓN</h3>
        <div class="info-grid mb-10">
            <div class="info-row">
                <span class="info-label">Firmado por:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de firma:</span>
                <span class="info-value">{{ $generated_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Hash de firma:</span>
                <span class="info-value"><span class="hash-code">{{ $signature_hash }}</span></span>
            </div>
            <div class="info-row">
                <span class="info-label">Código de verificación:</span>
                <span class="info-value"><span class="hash-code">{{ $verification_code }}</span></span>
            </div>
        </div>

        <div class="qr-container">
            <p style="margin-bottom: 8px; font-weight: bold; font-size: 9pt;">Escanea para verificar autenticidad:</p>
            <img src="{{ $qr_code_url }}" alt="QR Code de Verificación" width="150" height="150">
            <p style="margin-top: 8px; font-size: 7pt; color: #666;">
                O accede manualmente a:<br>
                <span class="hash-code">{{ route('reports.verify', ['code' => $verification_code]) }}</span>
            </p>
        </div>
    </div>

    {{-- Pie de Página Legal --}}
    <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 4px; font-size: 7pt; color: #666;">
        <p style="margin-bottom: 5px;">
            <strong>IMPORTANTE:</strong> Este documento ha sido generado automáticamente por el sistema certificado Agro365 
            y firmado electrónicamente conforme al Real Decreto 1311/2012 sobre uso sostenible de productos fitosanitarios.
        </p>
        <p>
            La autenticidad de este documento puede ser verificada escaneando el código QR o accediendo al enlace de verificación proporcionado.
            Cualquier modificación del contenido invalidará la firma electrónica.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div style="float: left;">
            Documento generado por Agro365 | {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div style="float: right;">
            Página <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->getFont("DejaVu Sans");
                    $pdf->page_text(520, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 7, array(0,0,0));
                }
            </script>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>

