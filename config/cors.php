<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://peppy-faun-569f3f.netlify.app',
        'http://localhost:3000',
        'http://localhost:5173',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Subscription-Status', 'Authorization'],
    'max_age' => 86400,
    'supports_credentials' => true,
];