<?php

/**
 * Script para preparar la base de datos de test
 * Ejecutar: php tests/setup-test-db.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Configurar para usar la base de datos de test
Config::set('database.connections.mariadb.database', 'agro365_test');

try {
    // Intentar crear la base de datos si no existe
    $databaseName = 'agro365_test';
    $connection = DB::connection('mariadb');
    
    // Conectar sin especificar la base de datos
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306",
        'agro365',
        'password'
    );
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "✅ Base de datos de test '{$databaseName}' creada o ya existe.\n";
    
    // Ejecutar migraciones
    echo "🔄 Ejecutando migraciones...\n";
    \Artisan::call('migrate', [
        '--database' => 'mariadb',
        '--env' => 'testing',
    ]);
    
    echo "✅ Migraciones ejecutadas.\n";
    echo "\n🎉 Base de datos de test lista para usar.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

