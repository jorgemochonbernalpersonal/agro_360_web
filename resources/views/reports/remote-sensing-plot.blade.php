<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe NDVI - {{ $plot->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .section {
            margin: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #10b981;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 5px 10px;
            font-weight: bold;
            width: 40%;
            background: #f3f4f6;
        }
        .info-value {
            display: table-cell;
            padding: 5px 10px;
        }
        .ndvi-box {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 10px;
            margin: 20px;
        }
        .ndvi-value {
            font-size: 48px;
            font-weight: bold;
            color: #059669;
        }
        .ndvi-label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
        .health-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        .health-excellent { background: #22c55e; color: white; }
        .health-good { background: #10b981; color: white; }
        .health-moderate { background: #eab308; color: white; }
        .health-poor { background: #f97316; color: white; }
        .health-critical { background: #ef4444; color: white; }
        .stats-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .stats-grid td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .stats-grid .stat-label {
            font-size: 10px;
            color: #6b7280;
        }
        .stats-grid .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .history-table th {
            background: #059669;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .history-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .history-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px;
            background: #f3f4f6;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>🛰️ Informe de Teledetección NDVI</h1>
        <p>{{ $plot->name }} - Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Plot Info -->
    <div class="section">
        <div class="section-title">📍 Información de la Parcela</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre</div>
                <div class="info-value">{{ $plot->name }}</div>
            </div>
            @if($plot->area)
            <div class="info-row">
                <div class="info-label">Área</div>
                <div class="info-value">{{ number_format($plot->area, 2) }} ha</div>
            </div>
            @endif
            @if($plot->municipality)
            <div class="info-row">
                <div class="info-label">Municipio</div>
                <div class="info-value">{{ $plot->municipality->name }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Propietario</div>
                <div class="info-value">{{ $user->name }}</div>
            </div>
        </div>
    </div>

    @if(isset($gdd))
    <!-- Phenology & GDD -->
    <div class="section">
        <div class="section-title">🍇 Fenología y Riesgo (GDD)</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Estado Fenológico</div>
                <div class="info-value">
                    <span style="font-size: 14px;">{{ $gdd['stage']['icon'] ?? '' }} <strong>{{ $gdd['stage']['name'] ?? 'N/A' }}</strong></span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">GDD Acumulado</div>
                <div class="info-value">
                    <strong>{{ $gdd['gdd_accumulated'] }}</strong> / {{ $gdd['gdd_target'] }} ({{ round(($gdd['gdd_accumulated'] / $gdd['gdd_target']) * 100) }}%)
                </div>
            </div>
            @if(isset($gdd['estimated_harvest_date']))
            <div class="info-row">
                <div class="info-label">Vendimia Estimada</div>
                <div class="info-value">{{ $gdd['estimated_harvest_date'] }} ({{ $gdd['days_to_harvest'] }} días)</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Disease Risks -->
    <div class="section">
        <div class="section-title">🦠 Riesgo de Enfermedades</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Enfermedad</th>
                    <th>Nivel</th>
                    <th>Obs.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gdd['risks'] ?? [] as $risk)
                <tr>
                    <td><strong>{{ $risk['name'] }}</strong></td>
                    <td>
                        <span style="color: {{ $risk['color'] }}; font-weight: bold;">
                            {{ strtoupper($risk['level'] === 'high' ? 'Alto' : ($risk['level'] === 'medium' ? 'Medio' : 'Bajo')) }}
                        </span>
                    </td>
                    <td>{{ $risk['message'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Current NDVI -->
    @if($latestData)
    <div class="ndvi-box">
        <div class="ndvi-value">{{ number_format($latestData->ndvi_mean, 2) }}</div>
        <div class="ndvi-label">NDVI Actual</div>
        <div class="health-badge health-{{ $latestData->health_status }}">
            {{ $latestData->health_emoji }} {{ $latestData->health_text }}
        </div>
        <div style="margin-top: 10px; font-size: 10px; color: #6b7280;">
            Última imagen: {{ $latestData->image_date->format('d/m/Y') }}
        </div>
    </div>
    @endif

    <!-- Statistics -->
    <div class="section">
        <div class="section-title">📊 Estadísticas (Últimos {{ $stats['data_points'] }} registros)</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-value">{{ $stats['average'] }}</div>
                    <div class="stat-label">Promedio NDVI</div>
                </td>
                <td>
                    <div class="stat-value">{{ $stats['min'] }}</div>
                    <div class="stat-label">Mínimo</div>
                </td>
                <td>
                    <div class="stat-value">{{ $stats['max'] }}</div>
                    <div class="stat-label">Máximo</div>
                </td>
                <td>
                    <div class="stat-value">
                        @if($stats['trend'] === 'increasing') 📈
                        @elseif($stats['trend'] === 'decreasing') 📉
                        @else ➡️
                        @endif
                    </div>
                    <div class="stat-label">Tendencia</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Historical Data -->
    @if($historicalData->count() > 0)
    <div class="section">
        <div class="section-title">📅 Histórico de Datos</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>NDVI</th>
                    <th>Estado</th>
                    <th>Nubosidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historicalData->take(15) as $data)
                <tr>
                    <td>{{ $data->image_date->format('d/m/Y') }}</td>
                    <td><strong>{{ number_format($data->ndvi_mean, 3) }}</strong></td>
                    <td>{{ $data->health_emoji }} {{ $data->health_text }}</td>
                    <td>{{ $data->cloud_coverage ? number_format($data->cloud_coverage, 0) . '%' : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($historicalData->count() > 15)
        <p style="margin-top: 10px; font-size: 9px; color: #6b7280; text-align: center;">
            Mostrando 15 de {{ $historicalData->count() }} registros
        </p>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Informe generado por Agro365 - Datos satelitales NASA MODIS & Clima Open-Meteo | {{ $generatedAt->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
