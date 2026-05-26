<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Período - {{ $plot->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #10b981;
        }
        .header h1 {
            color: #047857;
            margin: 0;
            font-size: 20px;
        }
        .header .plot-name {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .stats-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #047857;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table th {
            background-color: #047857;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .health-excellent { color: #059669; font-weight: bold; }
        .health-good { color: #10b981; font-weight: bold; }
        .health-moderate { color: #f59e0b; font-weight: bold; }
        .health-poor { color: #f97316; font-weight: bold; }
        .health-critical { color: #dc2626; font-weight: bold; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 10px;
            margin: 15px 0;
        }
        .info-box h3 {
            margin: 0 0 5px 0;
            color: #1e40af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('📊 Informe de Análisis Histórico NDVI') }}</h1>
        <div class="plot-name">{{ $plot->name }}</div>
        <div style="font-size: 10px; color: #999;">Generado: {{ $generated_at }}</div>
    </div>

    <div class="info-box">
        <h3>{{ __('Información del Período') }}</h3>
        <p style="margin: 5px 0; font-size: 10px;">
            <strong>{{ __('Período analizado:') }}</strong> {{ $stats['period'] }} días<br>
            <strong>{{ __('Total de registros:') }}</strong> {{ $stats['count'] }}<br>
            <strong>{{ __('Área de la parcela:') }}</strong> {{ number_format($plot->area ?? 0, 2) }} ha
        </p>
    </div>

    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-cell">
                <div class="stat-label">NDVI Promedio</div>
                <div class="stat-value">{{ number_format($stats['avg'], 3) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">NDVI Máximo</div>
                <div class="stat-value" style="color: #059669;">{{ number_format($stats['max'], 3) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">NDVI Mínimo</div>
                <div class="stat-value" style="color: #dc2626;">{{ number_format($stats['min'], 3) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Registros</div>
                <div class="stat-value" style="color: #3b82f6;">{{ $stats['count'] }}</div>
            </div>
        </div>
    </div>

    <h3 style="color: #047857; margin-top: 20px;">{{ __('Datos Históricos') }}</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('Fecha') }}</th>
                <th>{{ __('NDVI') }}</th>
                <th>{{ __('Estado de Salud') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historical as $record)
                <tr>
                    <td>{{ $record['fullDate'] }}</td>
                    <td><strong>{{ number_format($record['ndvi'], 3) }}</strong></td>
                    <td>
                        <span class="health-{{ $record['health_status'] ?? 'moderate' }}">
                            {{ ucfirst($record['health_status'] ?? 'N/A') }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>{{ __('Agro365 - Remote Sensing Dashboard') }}</strong></p>
        <p>{{ __('Datos satelitales NASA VIIRS/MODIS • 100% Gratuito') }}</p>
        <p>{{ __('Este informe contiene datos de teledetección para análisis profesional de viñedos') }}</p>
    </div>
</body>
</html>
