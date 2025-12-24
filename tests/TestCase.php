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
        
        // CRÍTICO: Verificar que usamos agro365_test, NO agro365
        $database = config('database.connections.'.config('database.default').'.database');
        
        if ($database !== 'agro365_test') {
            throw new \Exception(
                "🚨 PELIGRO: Los tests NO pueden ejecutarse en la base de datos '{$database}'. " .
                "Solo se permite 'agro365_test'. " .
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

        // SOLUCIÓN: Forzar variables de entorno de testing desde phpunit.xml
        // NO depender de .env.testing que puede no existir
        
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
