<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Forzar HTTPS en producción (debe ir primero)
        $middleware->append(\App\Http\Middleware\ForceHttps::class);
        
        // Middleware global de seguridad - aplica a todas las respuestas
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            'check.beta' => \App\Http\Middleware\CheckBetaAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
