<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe de Teledetección - {{ $plot->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #2E7D32;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2E7D32;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0;
            color: #2E7D32;
            font-size: 18px;
        }
        .info-box p {
            margin: 5px 0 0;
            color: #666;
            font-size: 10px;
        }
        .summary {
            background: #E8F5E9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary h2 {
            color: #2E7D32;
            margin: 0 0 10px;
            font-size: 14px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 20%;
            text-align: center;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2E7D32;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #2E7D32;
            color: white;
            padding: 8px;
            font-size: 9px;
            text-align: left;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .status-excellent { color: #2E7D32; font-weight: bold; }
        .status-good { color: #4CAF50; }
        .status-moderate { color: #FF9800; }
        .status-poor { color: #FF5722; }
        .status-critical { color: #F44336; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Informe de Teledetección</h1>
        <p><strong>Parcela:</strong> {{ $plot->name }} | <strong>Período:</strong> {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
        <p>Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <h2>📈 Resumen del Período</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ $summary['total_records'] }}</div>
                <div class="label">Registros</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['avg_ndvi'] !== null ? number_format($summary['avg_ndvi'], 3) : 'N/A' }}</div>
                <div class="label">NDVI Promedio</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['min_ndvi'] !== null ? number_format($summary['min_ndvi'], 3) : 'N/A' }}</div>
                <div class="label">NDVI Mínimo</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['max_ndvi'] !== null ? number_format($summary['max_ndvi'], 3) : 'N/A' }}</div>
                <div class="label">NDVI Máximo</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['avg_temperature'] !== null ? number_format($summary['avg_temperature'], 1) . '°C' : 'N/A' }}</div>
                <div class="label">Temp. Media</div>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>NDVI</th>
                <th>NDVI Mín</th>
                <th>NDVI Máx</th>
                <th>NDWI</th>
                <th>Estado</th>
                <th>Temp (°C)</th>
                <th>Precip (mm)</th>
                <th>Humedad (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $record)
            <tr>
                <td>{{ $record->image_date->format('d/m/Y') }}</td>
                <td><strong>{{ number_format($record->ndvi_mean, 3) }}</strong></td>
                <td>{{ number_format($record->ndvi_min, 3) }}</td>
                <td>{{ number_format($record->ndvi_max, 3) }}</td>
                <td>{{ $record->ndwi_mean ? number_format($record->ndwi_mean, 3) : 'N/A' }}</td>
                <td class="status-{{ $record->health_status }}">{{ $record->health_text }}</td>
                <td>{{ $record->temperature ? number_format($record->temperature, 1) : 'N/A' }}</td>
                <td>{{ $record->precipitation ? number_format($record->precipitation, 1) : 'N/A' }}</td>
                <td>{{ $record->humidity ? number_format($record->humidity, 0) : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #999; padding: 40px;">No hay datos de teledetección para el período seleccionado.</p>
    @endif

    <div class="footer">
        Agro365 - Software de Gestión Agrícola | {{ url('/') }}
    </div>
</body>
</html>
