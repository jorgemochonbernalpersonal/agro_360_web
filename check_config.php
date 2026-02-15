<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificando configuración NASA...\n\n";
echo "config('services.nasa_earthdata.mock'): " . var_export(config('services.nasa_earthdata.mock'), true) . "\n";
echo "env('NASA_EARTHDATA_MOCK'): " . var_export(env('NASA_EARTHDATA_MOCK'), true) . "\n";
echo "env('NASA_EARTHDATA_MOCK') raw: " . env('NASA_EARTHDATA_MOCK') . "\n";
