<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe Oficial - Tratamientos Fitosanitarios</title>
    <style>
        @page {
            margin: 25mm 20mm;
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 8pt;
                color: #666;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #2c3e50;
            background: #ffffff;
        }
        
        /* Header mejorado */
        .header {
            background: linear-gradient(135deg, #2c5530 0%, #2c5530 100%);
            color: white;
            padding: 25px 30px;
            margin: -25mm -20mm 30px -20mm;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header h1 {
            font-size: 20pt;
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .header .subtitle {
            font-size: 11pt;
            opacity: 0.95;
            font-weight: 300;
        }
        
        /* Contenedor principal con padding */
        .container {
            padding: 0 15px;
        }
        
        /* Info boxes mejorados */
        .info-box {
            border: 1.5px solid #e0e0e0;
            padding: 18px 20px;
            margin-bottom: 20px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-left: 4px solid #2c5530;
        }
        
        .info-box h3 {
            color: #2c5530;
            font-size: 12pt;
            margin-bottom: 12px;
            border-bottom: 2px solid #2c5530;
            padding-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-grid {
            width: 100%;
        }
        
        .info-row {
            margin-bottom: 10px;
            padding: 6px 0;
            border-bottom: 1px dotted #e8e8e8;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            display: inline-block;
            width: 35%;
            color: #555;
            font-size: 9.5pt;
        }
        
        .info-value {
            display: inline-block;
            width: 64%;
            color: #2c3e50;
            font-size: 9.5pt;
        }
        
        /* Sello oficial mejorado */
        .official-stamp {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            font-weight: bold;
            font-size: 13pt;
            text-align: center;
            border: 3px double #856404;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Tabla mejorada */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 25px;
            font-size: 8.5pt;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 6px;
            overflow: hidden;
        }
        
        thead {
            background: linear-gradient(135deg, #2c5530 0%, #2c5530 100%);
            color: white;
        }
        
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        
        th:last-child {
            border-right: none;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: top;
            color: #2c3e50;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Sección de firma mejorada */
        .signature-section {
            margin-top: 30px;
            padding: 25px;
            border: 2px solid #2c5530;
            background: linear-gradient(135deg, #f0f7f0 0%, #ffffff 100%);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .signature-section h3 {
            color: #2c5530;
            margin-bottom: 15px;
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #2c5530;
            padding-bottom: 10px;
        }
        
        /* QR Code mejorado */
        .qr-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 2px dashed #2c5530;
        }
        
        .qr-container p {
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 10pt;
            color: #2c5530;
        }
        
        .qr-container img {
            border: 4px solid #2c5530;
            padding: 15px;
            background: white;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            width: 150px;
            height: 150px;
            max-width: 150px;
            max-height: 150px;
        }
        
        .qr-container small {
            display: block;
            margin-top: 12px;
            font-size: 7.5pt;
            color: #666;
            line-height: 1.4;
        }
        
        /* Hash code mejorado */
        .hash-code {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8.5pt;
            border: 1px solid #ddd;
            word-break: break-all;
        }
        
        /* Footer mejorado */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30mm;
            background: #f8f9fa;
            border-top: 2px solid #e0e0e0;
            padding: 10mm 20mm;
            font-size: 8pt;
            color: #666;
        }
        
        /* Pie de página legal mejorado */
        .legal-footer {
            margin-top: 30px;
            padding: 18px;
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            border-radius: 6px;
            font-size: 8pt;
            color: #555;
            border-left: 4px solid #ffc107;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .legal-footer strong {
            color: #856404;
            font-size: 8.5pt;
        }
        
        /* Títulos de sección */
        h3.section-title {
            margin: 25px 0 15px;
            color: #2c5530;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2c5530;
        }
        
        /* Utilidades */
        .text-center {
            text-align: center;
        }
        
        .mb-15 {
            margin-bottom: 15px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        small {
            font-size: 7.5pt;
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
    
    {{-- Header mejorado --}}
    <div class="header">
        <div class="header-content" style="display: flex; align-items: center; gap: 15px;">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" style="height: 50px; width: auto;" alt="Agro365 Logo">
            @endif
            <div style="flex: 1;">
                <h1>INFORME OFICIAL DE TRATAMIENTOS FITOSANITARIOS</h1>
                <div class="subtitle">Cuaderno Digital Agrícola - Sistema Certificado Agro365</div>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- Datos de la Explotación --}}
        <div class="info-box">
            <h3>• DATOS DE LA EXPLOTACIÓN</h3>
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
                        @if($profile && $profile->city)
                            , {{ $profile->city }}
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
            <h3>• PERIODO DEL INFORME</h3>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Fecha inicio:</span>
                    <span class="info-value"><strong>{{ $period_start->format('d/m/Y') }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha fin:</span>
                    <span class="info-value"><strong>{{ $period_end->format('d/m/Y') }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total tratamientos:</span>
                    <span class="info-value"><strong>{{ $stats['total_treatments'] }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Superficie tratada total:</span>
                    <span class="info-value"><strong>{{ number_format($stats['total_area_treated'], 2, ',', '.') }} ha</strong></span>
                </div>
                @if(isset($stats['plots_affected']))
                <div class="info-row">
                    <span class="info-label">Parcelas afectadas:</span>
                    <span class="info-value"><strong>{{ $stats['plots_affected'] }}</strong></span>
                </div>
                @endif
            </div>
        </div>

        <div class="official-stamp">
            ✓ DOCUMENTO OFICIAL FIRMADO ELECTRÓNICAMENTE
        </div>

        {{-- Tabla de Tratamientos --}}
        <h3 class="section-title">📊 Detalle de Tratamientos Realizados</h3>
        
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
                            ? $treatment->activity_date->copy()->addDays($product->withdrawal_period_days)
                            : null;
                    @endphp
                    <tr>
                        <td>{{ $treatment->activity_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $treatment->plot->name }}</strong>
                            @if($treatment->plotPlanting)
                                <br><small>
                                    Plantación: {{ $treatment->plotPlanting->name }}
                                    @if($treatment->plotPlanting->grapeVariety)
                                        ({{ $treatment->plotPlanting->grapeVariety->name }})
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td>{{ $product->name ?? 'N/A' }}</td>
                        <td>{{ $product->registration_number ?? 'N/A' }}</td>
                        <td>{{ $phyto->dose_per_hectare }} L</td>
                        <td>{{ number_format($phyto->area_treated, 2) }} ha</td>
                        <td>{{ $phyto->target_pest ?? 'N/A' }}</td>
                        <td>
                            {{ $applicator?->name ?? 'N/A' }}
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
                        <td colspan="11" style="text-align: center; padding: 20px; color: #999;">
                            No se registraron tratamientos fitosanitarios en el periodo seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Sección de Firma y Verificación Mejorada --}}
        <div class="signature-section">
            <h3>🔐 Firma Electrónica y Verificación</h3>
            <div class="info-grid mb-15">
                <div class="info-row">
                    <span class="info-label">Firmado por:</span>
                    <span class="info-value"><strong>{{ $user->name }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de firma:</span>
                    <span class="info-value"><strong>{{ $generated_at->format('d/m/Y H:i:s') }}</strong></span>
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
                <p>Escanea para verificar autenticidad</p>
                <img src="{{ $qr_code_url }}" alt="QR Code de Verificación" style="width: 150px; height: 150px;">
                <small>
                    O accede manualmente a:<br>
                    <span class="hash-code" style="font-size: 7pt;">{{ route('reports.verify', ['code' => $verification_code]) }}</span>
                </small>
            </div>
        </div>

        {{-- Pie de Página Legal Mejorado --}}
        <div class="legal-footer">
            <p style="margin-bottom: 8px;">
                <strong>⚠️ IMPORTANTE:</strong> Este documento ha sido generado automáticamente por el sistema certificado Agro365 
                y firmado electrónicamente conforme al Real Decreto 1311/2012 sobre uso sostenible de productos fitosanitarios.
            </p>
            <p>
                La autenticidad de este documento puede ser verificada escaneando el código QR o accediendo al enlace de verificación proporcionado.
                Cualquier modificación del contenido invalidará la firma electrónica.
            </p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div style="float: left;">
            <strong>Agro365</strong> - Sistema de Gestión Agrícola Certificado<br>
            Documento generado el {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div style="float: right; text-align: right;">
            <strong>Página {PAGE_NUM} de {PAGE_COUNT}</strong>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>

