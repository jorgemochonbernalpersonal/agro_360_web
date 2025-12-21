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
];

