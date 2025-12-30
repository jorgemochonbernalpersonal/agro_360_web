<?php

use App\Models\Plot;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Support\Facades\Cache;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure we don't mock for this test if possible, or we want to test the fallback?
// The user has mock=false in .env? 
// ENV is: OPEN_METEO_MOCK=false
// So it should hit the API.

$plot = Plot::first();
if (!$plot) {
    // Create a dummy object if needed, but Plot is a model.
    // simpler to just skip if no plot, but user has plots.
    echo "No plots found, trying simple object if possible? No, type hint requires Plot.\n";
    // We can just rely on the fallback coords in service if plot has no geometry
    $plot = new Plot();
    $plot->id = 1; 
}

echo "Testing WeatherService for Plot ID {$plot->id}...\n";

$service = new WeatherService();

echo "--- Calling getCurrentWeather(forceRefresh: true) ---\n";
try {
    $data = $service->getCurrentWeather($plot, true);
    
    echo "Result Status: " . ($data['success'] ? 'Available' : 'Failed') . "\n";
    echo "Temperature: " . ($data['temperature'] ?? 'NULL') . "\n";
    echo "Mock Data? " . (isset($data['mock']) ? 'YES' : 'NO') . "\n";
    
    if (isset($data['temperature']) && !is_null($data['temperature'])) {
        echo "VERIFICATION PASSED: Data retrieved successfully.\n";
    } else {
        echo "VERIFICATION FAILED: Temperature is missing.\n";
    }
    
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
