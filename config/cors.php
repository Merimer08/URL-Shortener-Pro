<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de CORS: se determina qué orígenes, métodos y cabeceras
    | están permitidos para peticiones desde navegadores. 
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Leer desde el .env (CORS_ALLOWED_ORIGINS)
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // más simple que listarlos a mano

    'exposed_headers' => [],

    'max_age' => 0,

    // Con Bearer tokens = false; si algún día usas cookies con Sanctum → true
    'supports_credentials' => false,
];
