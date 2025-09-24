<?php

return [

    // Solo rutas de API
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Desde .env → CORS_ALLOWED_ORIGINS
    // (p.ej. "http://localhost:5173,https://url-shortener-frontend.up.railway.app")
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Modo tokens → NO credenciales
    'supports_credentials' => false,
];
