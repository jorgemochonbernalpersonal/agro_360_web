<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Keys Prefix
    |--------------------------------------------------------------------------
    |
    | Prefijo para todas las keys de cache de la aplicación.
    | Útil para evitar colisiones en entornos compartidos.
    |
    */
    'prefix' => env('CACHE_PREFIX', 'agro365'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live) por tipo
    |--------------------------------------------------------------------------
    |
    | Duración en segundos de cada tipo de cache.
    |
    */
    'ttl' => [
        // Cache de corta duración (datos que cambian frecuentemente)
        'short' => 300,      // 5 minutos
        
        // Cache media (datos que cambian ocasionalmente)
        'medium' => 3600,    // 1 hora
        
        // Cache larga (datos que raramente cambian)
        'long' => 86400,     // 24 horas
        
        // Cache muy larga (catálogos, geografía)
        'very_long' => 604800, // 1 semana
        
        // Específicos
        'campaigns' => 86400,      // 24 horas
        'plots' => 43200,          // 12 horas
        'activities' => 3600,      // 1 hora
        'statistics' => 7200,      // 2 horas
        'user_session' => 3600,    // 1 hora
        'catalogs' => 604800,      // 1 semana
        'geography' => 604800,     // 1 semana
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Tags para organizar y limpiar cache por grupos.
    |
    */
    'tags' => [
        'campaigns' => 'campaigns',
        'plots' => 'plots',
        'activities' => 'activities',
        'users' => 'users',
        'catalogs' => 'catalogs',
        'geography' => 'geography',
        'statistics' => 'statistics',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming
    |--------------------------------------------------------------------------
    |
    | Configuración para precalentar cache al iniciar.
    |
    */
    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', false),
        'items' => [
            'geography',
            'catalogs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Invalidation Rules
    |--------------------------------------------------------------------------
    |
    | Reglas automáticas de invalidación de cache.
    |
    */
    'auto_invalidate' => [
        'on_model_update' => true,
        'on_model_delete' => true,
        'cascade' => true, // Invalidar caches relacionados
    ],
];
