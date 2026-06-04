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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'apple' => [
        // Bundle ID de la app iOS — se verifica en el campo 'aud' del JWT de Apple
        'bundle_id' => env('APPLE_BUNDLE_ID', 'com.agro365.mobile'),
    ],

    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'site_key' => env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), // Clave de prueba
        'secret_key' => env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'), // Clave de prueba
    ],

    /*
    |--------------------------------------------------------------------------
    | Copernicus Data Space — Sentinel-2 L2A (100% GRATIS)
    |--------------------------------------------------------------------------
    |
    | Proporciona imágenes Sentinel-2 a 10m de resolución cada 5 días.
    | Regístrate gratis en: https://dataspace.copernicus.eu/ → Register
    |
    */

    'copernicus' => [
        'username' => env('COPERNICUS_USERNAME'),
        'password' => env('COPERNICUS_PASSWORD'),
    ],

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
        'mock' => filter_var(env('NASA_EARTHDATA_MOCK', 'false'), FILTER_VALIDATE_BOOLEAN), // false = usa datos reales de NASA
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
        'mock' => filter_var(env('OPEN_METEO_MOCK', 'false'), FILTER_VALIDATE_BOOLEAN), // false = usa datos reales
    ],

    /*
    |--------------------------------------------------------------------------
    | VeriFactu / SIF — Agencia Tributaria
    |--------------------------------------------------------------------------
    |
    | Configuración para el envío de facturas al sistema Verifactu de la AEAT.
    | Entornos: testing (prewww1.aeat.es) | production (www1.agenciatributaria.gob.es)
    |
    */

    'sif_cert' => [
        'path' => env('SIF_CERT_PATH', ''),
        'password' => env('SIF_CERT_PASSWORD', ''),
    ],

    'sif_aeat' => [
        'environment' => env('SIF_ENVIRONMENT', 'testing'),
        // WSDL — SistemaFacturacion.wsdl (NOT SuministroLR.wsdl which is SII/large companies)
        'wsdl' => env('SIF_AEAT_WSDL', base_path('resources/wsdl/SistemaFacturacion.wsdl')),
        // Endpoint test:  https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP
        // Endpoint prod:  https://www1.agenciatributaria.gob.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP
        'endpoint' => env('SIF_AEAT_ENDPOINT', 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP'),
    ],

    // Software vendor identification (SistemaInformatico block — mandatory in XML)
    'sif_software' => [
        'vendor_name' => env('SIF_VENDOR_NAME', 'Agro365'),
        'vendor_nif' => env('SIF_VENDOR_NIF', ''),
        'name' => env('SIF_SOFTWARE_NAME', 'Agro365'),
        'id' => env('SIF_SOFTWARE_ID', 'A3'),        // max 2 chars
        'version' => env('SIF_SOFTWARE_VERSION', '1.0.0'),
        'install_id' => env('SIF_INSTALL_ID', 'AGR001'),     // max 100 chars
    ],

];
