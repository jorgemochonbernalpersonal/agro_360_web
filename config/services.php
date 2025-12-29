<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v2
    |--------------------------------------------------------------------------
    |
    | Configuración de Google reCAPTCHA v2 para protección contra bots.
    | Obtén tus claves en: https://www.google.com/recaptcha/admin
    |
    */

    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'site_key' => env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), // Clave de prueba
        'secret_key' => env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'), // Clave de prueba
    ],

    /*
    |--------------------------------------------------------------------------
    | Copernicus Data Space API
    |--------------------------------------------------------------------------
    |
    | Configuración para acceder a datos de Sentinel-2 via Copernicus.
    | Regístrate en: https://dataspace.copernicus.eu/ (DEPRECATED)
    |
    */

    /*
    |--------------------------------------------------------------------------
    | NASA Earthdata API (100% GRATIS) - EN USO
    |--------------------------------------------------------------------------
    |
    | API gratuita sin límites de uso.
    | Usa datos MODIS/VIIRS para NDVI.
    | Regístrate gratis en: https://urs.earthdata.nasa.gov/
    |
    */

    'nasa_earthdata' => [
        'mock' => env('NASA_EARTHDATA_MOCK', true), // true = usa datos simulados
        'username' => env('NASA_EARTHDATA_USERNAME'),
        'password' => env('NASA_EARTHDATA_PASSWORD'),
        'api_url' => env('NASA_EARTHDATA_API_URL', 'https://appeears.earthdatacloud.nasa.gov/api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Open-Meteo API (100% GRATIS - SIN REGISTRO)
    |--------------------------------------------------------------------------
    |
    | API meteorológica gratuita sin necesidad de registro.
    | Incluye: temperatura, lluvia, humedad, viento, suelo, radiación solar.
    | https://open-meteo.com/
    |
    */

    'open_meteo' => [
        'mock' => env('OPEN_METEO_MOCK', true), // true = usa datos simulados
    ],

];
