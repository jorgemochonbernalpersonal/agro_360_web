<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    /**
     * Setup the test environment.
     * 🛡️ PROTECCIÓN: Verificar que NO estamos usando la BD de producción
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // CRÍTICO: Verificar que usamos BD de test, NO producción (agro365)
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $allowedDatabases = ['agro365_test', ':memory:'];
        
        if (! in_array($database, $allowedDatabases)) {
            throw new \Exception(
                "🚨 PELIGRO: Los tests NO pueden ejecutarse en la base de datos '{$database}'. " .
                "Solo se permite agro365_test o :memory: (SQLite). " .
                "Revisa tu configuración en phpunit.xml"
            );
        }
    }
}

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
