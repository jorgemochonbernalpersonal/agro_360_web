<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Open-Meteo API...\n";

// Coordinates (Madrid)
$lat = 40.4168;
$lon = -3.7038;

$baseUrl = 'https://api.open-meteo.com/v1/forecast';
$params = [
    'latitude' => $lat,
    'longitude' => $lon,
    'current' => 'temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,weather_code',
    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
    'timezone' => 'Europe/Madrid',
];

echo "URL: $baseUrl\n";
echo "Params: " . print_r($params, true) . "\n";

try {
    // using curl directly to avoid Laravel Http wrapper complexity if needed, 
    // but better to use Laravel wrapper if possible.
    // However, in a standalone script, we can just use Guzzle directly or Http facade.
    
    $response = Http::timeout(30)->get($baseUrl, $params);
    
    $output = "Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $json = $response->json();
        $output .= "Response JSON keys: " . implode(', ', array_keys($json)) . "\n";
        
        if (isset($json['current'])) {
            $output .= "Current Data:\n" . print_r($json['current'], true);
        } else {
            $output .= "MISSING 'current' KEY!\n";
        }
        
        if (isset($json['daily'])) {
            $output .= "Daily Data:\n" . print_r($json['daily'], true);
        } else {
            $output .= "MISSING 'daily' KEY!\n";
        }

        // Check if values are null
        if (isset($json['current']['temperature_2m']) && is_null($json['current']['temperature_2m'])) {
            $output .= "ALERT: temperature_2m is NULL!\n";
        }
        
    } else {
        $output .= "Response Failed!\n";
        $output .= $response->body() . "\n";
    }

    file_put_contents('debug_output.txt', $output);


} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
