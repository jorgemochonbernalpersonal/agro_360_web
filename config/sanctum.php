<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains (SPA web — no afecta al móvil que usa tokens)
    |--------------------------------------------------------------------------
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', [
        'localhost',
        'localhost:3000',
        '127.0.0.1',
        '127.0.0.1:8000',
        '::1',
        parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST),
    ]))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    */
    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiración de tokens de acceso (en minutos)
    | null = sin expiración (no recomendado en producción)
    | 43200 = 30 días (móvil)
    |--------------------------------------------------------------------------
    */
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 43200), // 30 días

    /*
    |--------------------------------------------------------------------------
    | Token prefix — ayuda a identificar tokens Sanctum en logs/secretos
    |--------------------------------------------------------------------------
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
