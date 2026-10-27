<?php

$frontend = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');
$extra = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))));

$origins = array_filter([$frontend, ...$extra]);

if (env('APP_ENV', 'production') !== 'production') {
    $origins[] = 'http://localhost:5173';
    $origins[] = 'http://127.0.0.1:5173';
}

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique($origins)),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,
];
