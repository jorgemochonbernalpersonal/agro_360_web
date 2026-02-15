<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Plot;
use App\Models\PlotRemoteSensing;

$userId = 41;

echo "🛰️  Generando datos de Remote Sensing para usuario {$userId}...\n\n";

$plots = Plot::where('viticulturist_id', $userId)->get();

if ($plots->isEmpty()) {
    echo "❌ No se encontraron parcelas para el usuario {$userId}\n";
    exit(1);
}

echo "📊 Parcelas encontradas: {$plots->count()}\n\n";

$totalRecords = 0;

foreach ($plots as $plot) {
    echo "  🌿 Procesando: {$plot->name} (ID: {$plot->id})\n";
    
    // Generar 90 días de datos (cada 7 días)
    $seed = $plot->id * 1000;
    mt_srand($seed);
    
    $baseNDVI = 0.55 + (mt_rand(0, 200) / 1000);
    $records = 0;
    
    for ($day = 90; $day >= 0; $day -= 7) {
        $date = now()->subDays($day);
        
        // Seasonal trend
        $seasonalFactor = sin(((90 - $day) / 90) * M_PI);
        $randomVariation = (mt_rand(-50, 50) / 1000);
        
        $ndvi = $baseNDVI + ($seasonalFactor * 0.15) + $randomVariation;
        $ndvi = max(0.2, min(0.85, $ndvi));
        
        $ndwi = ($ndvi * 0.8) + (mt_rand(-30, 30) / 1000);
        $evi = $ndvi * 1.1;
        $temp = 18 + ($seasonalFactor * 12) + mt_rand(-3, 3);
        $precipitation = mt_rand(0, 15);
        $soilMoisture = 0.15 + (mt_rand(0, 150) / 1000);
        
        $healthStatus = match (true) {
            $ndvi >= 0.7 => 'excellent',
            $ndvi >= 0.5 => 'good',
            $ndvi >= 0.3 => 'moderate',
            $ndvi >= 0.15 => 'poor',
            default => 'critical',
        };
        
        PlotRemoteSensing::create([
            'plot_id' => $plot->id,
            'image_date' => $date,
            'date' => $date,
            'ndvi_mean' => $ndvi,
            'ndvi_min' => $ndvi - 0.05,
            'ndvi_max' => $ndvi + 0.05,
            'ndvi_stddev' => mt_rand(50, 150) / 1000,
            'ndwi_mean' => $ndwi,
            'ndwi_min' => $ndwi - 0.03,
            'ndwi_max' => $ndwi + 0.03,
            'evi_mean' => $evi,
            'cloud_coverage' => mt_rand(0, 30),
            'image_source' => 'MODIS_MOD13Q1',
            'health_status' => $healthStatus,
            'trend' => $seasonalFactor > 0.5 ? 'increasing' : 'decreasing',
            'temperature' => $temp,
            'temperature_min' => $temp - 5,
            'temperature_max' => $temp + 7,
            'precipitation' => $precipitation,
            'humidity' => 40 + mt_rand(0, 40),
            'wind_speed' => mt_rand(5, 25),
            'soil_moisture' => $soilMoisture,
            'soil_temperature' => $temp - 2,
            'solar_radiation' => 15 + mt_rand(0, 10),
            'et0' => 3 + mt_rand(0, 3),
            'sunshine_hours' => 8 + mt_rand(0, 6),
            'metadata' => [
                'data_source' => 'mock',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
        
        $records++;
    }
    
    echo "     ✅ {$records} registros creados\n";
    $totalRecords += $records;
}

echo "\n✅ ¡Completado!\n";
echo "   - Total parcelas: {$plots->count()}\n";
echo "   - Total registros RS: {$totalRecords}\n";
echo "\n💡 Ahora puedes calcular métricas avanzadas:\n";
echo "   php artisan remote-sensing:calculate-advanced-metrics\n";
