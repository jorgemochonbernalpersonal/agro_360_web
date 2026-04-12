<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Lista de orígenes permitidos para peticiones CORS.
    | Usar ['*'] para permitir todos los orígenes (no recomendado en producción).
    |
    */
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') !== null
        ? (env('CORS_ALLOWED_ORIGINS') === '*' ? ['*'] : explode(',', env('CORS_ALLOWED_ORIGINS', '')))
        : (in_array(env('APP_ENV', 'production'), ['local', 'testing']) ? ['*'] : []),

    /*
    |--------------------------------------------------------------------------
    | CORS Allowed Methods
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
    |--------------------------------------------------------------------------
    | CORS Allowed Headers
    |--------------------------------------------------------------------------
    */
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],

    /*
    |--------------------------------------------------------------------------
    | CORS Max Age
    |--------------------------------------------------------------------------
    |
    | Tiempo en segundos que el navegador puede cachear la respuesta preflight.
    |
    */
    'max_age' => 86400, // 24 horas
];
