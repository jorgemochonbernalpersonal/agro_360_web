<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Informes Oficiales
    |--------------------------------------------------------------------------
    |
    | Configuración relacionada con la generación y gestión de informes
    | oficiales con firma digital.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Días Máximos para Invalidar
    |--------------------------------------------------------------------------
    |
    | Número máximo de días desde la firma en que un informe puede ser
    | invalidado. Después de este período, el informe queda permanentemente
    | válido por razones de seguridad y cumplimiento legal.
    |
    */

    'max_days_to_invalidate' => env('REPORTS_MAX_DAYS_TO_INVALIDATE', 30),

    /*
    |--------------------------------------------------------------------------
    | Versión de Firma
    |--------------------------------------------------------------------------
    |
    | Versión del formato de firma digital utilizado. Se incluye en cada
    | firma para permitir migraciones futuras y compatibilidad.
    |
    */

    'signature_version' => env('REPORTS_SIGNATURE_VERSION', '1.0'),

    /*
    |--------------------------------------------------------------------------
    | Algoritmo de Hash
    |--------------------------------------------------------------------------
    |
    | Algoritmo utilizado para generar los hashes de firma.
    | Opciones: sha256, sha512
    |
    */

    'signature_algorithm' => env('REPORTS_SIGNATURE_ALGORITHM', 'sha256'),

    /*
    |--------------------------------------------------------------------------
    | Incluir Hash del PDF
    |--------------------------------------------------------------------------
    |
    | Si está activado, el hash del PDF se incluye en los datos firmados,
    | garantizando que cualquier modificación del PDF invalide la firma.
    |
    */

    'include_pdf_hash_in_signature' => env('REPORTS_INCLUDE_PDF_HASH', true),

    /*
    |--------------------------------------------------------------------------
    | Almacenamiento de PDFs
    |--------------------------------------------------------------------------
    |
    | Configuración para el almacenamiento de archivos PDF e informes.
    |
    */

    'storage' => [
        'disk' => 'local',
        'path' => 'official_reports',
        'memory_limit' => '512M', // Límite de memoria para generación de PDFs
    ],

    /*
    |--------------------------------------------------------------------------
    | Política de Retención de PDFs
    |--------------------------------------------------------------------------
    |
    | Número de días para conservar los archivos PDF antes de poder ser
    | eliminados. Establecer en null para conservar PDFs indefinidamente.
    | Los registros de base de datos siempre se conservan independientemente.
    |
    */

    'pdf_retention_days' => env('REPORTS_PDF_RETENTION_DAYS', 365), // 1 año

    /*
    |--------------------------------------------------------------------------
    | Tipos de Informes
    |--------------------------------------------------------------------------
    |
    | Tipos de informes disponibles y sus metadatos.
    | Solo los tipos implementados deben listarse aquí.
    |
    */

    'types' => [
        'phytosanitary_treatments' => [
            'name' => 'Tratamientos Fitosanitarios',
            'icon' => '🧪',
            'description' => 'Informe oficial de tratamientos fitosanitarios aplicados',
            'implemented' => true,
        ],
        'full_digital_notebook' => [
            'name' => 'Cuaderno Digital Completo',
            'icon' => '📔',
            'description' => 'Informe completo de todas las actividades de una campaña',
            'implemented' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Códigos QR
    |--------------------------------------------------------------------------
    |
    | Configuración para la generación de códigos QR en los PDFs.
    |
    */

    'qr_code' => [
        'size' => 300,
        'error_correction' => 'H', // Corrección de error Alta
        'margin' => 2,
        'primary_api' => 'https://api.qrserver.com/v1/create-qr-code/',
        'fallback_api' => 'https://chart.googleapis.com/chart',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificaciones por Email
    |--------------------------------------------------------------------------
    |
    | Configuración de notificaciones por email cuando se generan informes.
    |
    */

    'notifications' => [
        'send_on_generation' => env('REPORTS_SEND_EMAIL_ON_GENERATION', true),
        'send_on_failure' => env('REPORTS_SEND_EMAIL_ON_FAILURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Colas
    |--------------------------------------------------------------------------
    |
    | Configuración para procesamiento en segundo plano de informes.
    |
    */

    'queue' => [
        'enabled' => env('REPORTS_QUEUE_ENABLED', true),
        'connection' => env('REPORTS_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('REPORTS_QUEUE_NAME', 'default'),
        'timeout' => 300, // 5 minutos
        'tries' => 3,
        'backoff' => [60, 120, 300], // 1min, 2min, 5min
    ],
];

