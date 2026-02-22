<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VeriFactu Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing with AEAT sandbox, or 'production'
    | for live submissions. Default: sandbox.
    |
    */
    'environment' => env('VERIFACTU_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | AEAT Endpoints
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'sandbox' => [
            'base_url' => 'https://prewww2.aeat.es',
            'registro' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/RegistroFacturacion',
            'anulacion' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/AnulacionFacturacion',
            'consulta' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/ConsultaFacturacion',
            'qr_validation_url' => 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR',
        ],
        'production' => [
            'base_url' => 'https://www2.agenciatributaria.gob.es',
            'registro' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/RegistroFacturacion',
            'anulacion' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/AnulacionFacturacion',
            'consulta' => '/wlpl/TIKE-CONT/ws/SistemaFacturacion/ConsultaFacturacion',
            'qr_validation_url' => 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Validation URL
    |--------------------------------------------------------------------------
    */
    'qr_validation_url' => env('VERIFACTU_QR_URL', 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR'),

    /*
    |--------------------------------------------------------------------------
    | Software Information (defaults, overridable per tenant in company_profiles)
    |--------------------------------------------------------------------------
    */
    'software' => [
        'name' => env('VERIFACTU_SOFTWARE_NAME', 'Factu01'),
        'id' => env('VERIFACTU_SOFTWARE_ID', 'FACTU01-001'),
        'version' => env('VERIFACTU_SOFTWARE_VERSION', '1.0'),
        'nif' => env('VERIFACTU_SOFTWARE_NIF', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Submission Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retries' => [
        'max_attempts' => 3,
        'backoff_minutes' => [1, 5, 15],
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificate Storage
    |--------------------------------------------------------------------------
    | Directory relative to storage/ where certificate PEM files are stored.
    |
    */
    'cert_storage_path' => env('VERIFACTU_CERT_PATH', 'app/private/certificates'),

];
