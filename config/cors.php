<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],

    // Usa .env para orígenes permitidos (frontend prod + dev)
    // En tu .env: CORS_ALLOWED_ORIGINS=https://url-shortener-frontend.up.railway.app,http://localhost:5173,http://127.0.0.1:5173
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'https://url-shortener-frontend.up.railway.app,http://localhost:5173,http://127.0.0.1:5173')),

    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
