<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe Oficial - Tratamientos Fitosanitarios</title>
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
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .mb-10 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <div class="header">
        <h1>📋 INFORME OFICIAL DE TRATAMIENTOS FITOSANITARIOS</h1>
        <div class="subtitle">Cuaderno Digital Agrícola - Agro365</div>
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
                <span class="info-label">Teléfono:</span>
                <span class="info-value">{{ $profile->phone ?? 'No especificado' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
        </div>
    </div>

    {{-- Periodo del Informe --}}
    <div class="info-box">
        <h3>PERIODO DEL INFORME</h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Fecha inicio:</span>
                <span class="info-value">{{ $period_start->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha fin:</span>
                <span class="info-value">{{ $period_end->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total tratamientos:</span>
                <span class="info-value">{{ $stats['total_treatments'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Superficie tratada total:</span>
                <span class="info-value">{{ number_format($stats['total_area_treated'], 2, ',', '.') }} ha</span>
            </div>
            @if(isset($stats['plots_affected']))
            <div class="info-row">
                <span class="info-label">Parcelas afectadas:</span>
                <span class="info-value">{{ $stats['plots_affected'] }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="official-stamp">
        ✓ DOCUMENTO OFICIAL FIRMADO ELECTRÓNICAMENTE
    </div>

    {{-- Tabla de Tratamientos --}}
    <h3 style="margin: 15px 0 8px; color: #2c5530; font-size: 11pt;">DETALLE DE TRATAMIENTOS REALIZADOS</h3>
    
    <table>
        <thead>
            <tr>
                <th style="width: 7%;">Fecha</th>
                <th style="width: 11%;">Parcela</th>
                <th style="width: 14%;">Producto</th>
                <th style="width: 9%;">Nº Registro</th>
                <th style="width: 7%;">Dosis/ha</th>
                <th style="width: 6%;">Área</th>
                <th style="width: 10%;">Plaga</th>
                <th style="width: 11%;">Aplicador</th>
                <th style="width: 9%;">Condiciones</th>
                <th style="width: 7%;">Plazo</th>
                <th style="width: 9%;">F. Segura</th>
            </tr>
        </thead>
        <tbody>
            @forelse($treatments as $treatment)
                @php
                    $phyto = $treatment->phytosanitaryTreatment;
                    $product = $phyto->product ?? null;
                    $applicator = $treatment->crewMember ?? $treatment->crew;
                    $safeDate = $product && $product->withdrawal_period_days 
                        ? $treatment->activity_date->addDays($product->withdrawal_period_days)
                        : null;
                @endphp
                <tr>
                    <td>{{ $treatment->activity_date->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $treatment->plot->name }}</strong>
                        @if($treatment->plotPlanting)
                            <br><small class="text-gray-600">
                                Plantación: {{ $treatment->plotPlanting->name }}
                                @if($treatment->plotPlanting->grapeVariety)
                                    ({{ $treatment->plotPlanting->grapeVariety->name }})
                                @endif
                            </small>
                        @endif
                        <br><small>
                            @if($treatment->plot->sigpacCodes->isNotEmpty())
                                SIGPAC: {{ $treatment->plot->sigpacCodes->first()->full_code }}
                            @endif
                        </small>
                    </td>
                    <td>{{ $product->name ??'N/A' }}</td>
                    <td>{{ $product->registration_number ?? 'N/A' }}</td>
                    <td>{{ $phyto->dose_per_hectare }} {{ $product->unit ?? 'L' }}</td>
                    <td>{{ number_format($phyto->area_treated, 2) }} ha</td>
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
                            {{ $treatment->temperature ?? '--' }}°C<br>
                            {{ $phyto->wind_speed ?? '--' }} km/h<br>
                            H: {{ $phyto->humidity ?? '--' }}%
                        </small>
                    </td>
                    <td>{{ $product->withdrawal_period_days ?? 0 }} días</td>
                    <td>{{ $safeDate ? $safeDate->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 15px; color: #999;">
                        No se registraron tratamientos fitosanitarios en el periodo seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

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
