<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if (file_exists(__DIR__.'/../.env.testing')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/..', '.env.testing');
            $dotenv->load();
        }

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
