<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Database\Seeders\CompleteTestUserSeeder;
use Illuminate\Support\Facades\Artisan;

$userId = 41;

echo "🚀 Ejecutando CompleteTestUserSeeder para usuario ID: {$userId}\n\n";

$seeder = new CompleteTestUserSeeder();

// Simular el command object para el seeder
$seeder->setCommand(new class {
    public function info($message) {
        echo "ℹ️  {$message}\n";
    }
    
    public function warn($message) {
        echo "⚠️  {$message}\n";
    }
    
    public function error($message) {
        echo "❌ {$message}\n";
    }
});

try {
    $seeder->run($userId);
    echo "\n✅ ¡Seeder ejecutado exitosamente!\n";
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
