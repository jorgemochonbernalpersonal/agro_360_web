<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe Global de Teledetección</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
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
            font-size: 11px;
            opacity: 0.9;
        }
        .section {
            margin: 15px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #10b981;
        }
        .summary-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            padding: 20px;
            border-radius: 10px;
            margin: 15px;
            text-align: center;
        }
        .big-number {
            font-size: 36px;
            font-weight: bold;
            color: #059669;
        }
        .stats-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-grid td {
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            width: 20%;
        }
        .stats-grid .stat-value {
            font-size: 18px;
            font-weight: bold;
        }
        .stats-grid .stat-label {
            font-size: 9px;
            color: #6b7280;
        }
        .excellent { color: #22c55e; }
        .good { color: #10b981; }
        .moderate { color: #eab308; }
        .poor { color: #f97316; }
        .critical { color: #ef4444; }
        .plots-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .plots-table th {
            background: #059669;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
        }
        .plots-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .plots-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .health-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-excellent { background: #dcfce7; color: #16a34a; }
        .badge-good { background: #d1fae5; color: #059669; }
        .badge-moderate { background: #fef9c3; color: #ca8a04; }
        .badge-poor { background: #fed7aa; color: #ea580c; }
        .badge-critical { background: #fecaca; color: #dc2626; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px;
            background: #f3f4f6;
            text-align: center;
            font-size: 8px;
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
        <h1>🛰️ Informe Global de Teledetección</h1>
        <p>{{ $user->name }} - Últimos {{ $days }} días - Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <div class="big-number">{{ number_format($stats['average_ndvi'], 2) }}</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">NDVI Promedio Global</div>
        <div style="font-size: 11px; margin-top: 10px;">
            {{ $stats['total_plots'] }} parcelas analizadas
        </div>
    </div>

    <!-- Health Distribution -->
    <div class="section">
        <div class="section-title">📊 Distribución de Estado de Salud</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-value excellent">{{ $stats['excellent'] }}</div>
                    <div class="stat-label">🌿 Excelente</div>
                </td>
                <td>
                    <div class="stat-value good">{{ $stats['good'] }}</div>
                    <div class="stat-label">🌱 Bueno</div>
                </td>
                <td>
                    <div class="stat-value moderate">{{ $stats['moderate'] }}</div>
                    <div class="stat-label">🌾 Moderado</div>
                </td>
                <td>
                    <div class="stat-value poor">{{ $stats['poor'] }}</div>
                    <div class="stat-label">🍂 Bajo</div>
                </td>
                <td>
                    <div class="stat-value critical">{{ $stats['critical'] }}</div>
                    <div class="stat-label">🥀 Crítico</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- All Plots Table -->
    <div class="section">
        <div class="section-title">📋 Detalle por Parcela</div>
        <table class="plots-table">
            <thead>
                <tr>
                    <th>Parcela</th>
                    <th>NDVI</th>
                    <th>Estado</th>
                    <th>Tendencia</th>
                    <th>Última Imagen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plotsData as $item)
                <tr>
                    <td><strong>{{ $item['plot']->name }}</strong></td>
                    <td>{{ number_format($item['data']->ndvi_mean, 3) }}</td>
                    <td>
                        <span class="health-badge badge-{{ $item['data']->health_status }}">
                            {{ $item['data']->health_emoji }} {{ $item['data']->health_text }}
                        </span>
                    </td>
                    <td>{{ $item['data']->trend_icon }}</td>
                    <td>{{ $item['data']->image_date->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Alerts Section -->
    @php
        $alerts = collect($plotsData)->filter(fn($item) => in_array($item['data']->health_status, ['poor', 'critical']));
    @endphp
    @if($alerts->count() > 0)
    <div class="section" style="border-color: #fecaca; background: #fef2f2;">
        <div class="section-title" style="color: #dc2626; border-color: #ef4444;">⚠️ Parcelas con Alerta</div>
        <p style="margin-bottom: 10px;">Las siguientes parcelas requieren atención:</p>
        <ul style="margin-left: 20px;">
            @foreach($alerts as $alert)
            <li style="margin-bottom: 5px;">
                <strong>{{ $alert['plot']->name }}</strong>: 
                NDVI {{ number_format($alert['data']->ndvi_mean, 2) }} - {{ $alert['data']->health_text }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Informe generado por Agro365 - Sistema de Teledetección Sentinel-2 | {{ $generatedAt->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
