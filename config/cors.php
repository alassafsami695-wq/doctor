<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://peppy-fawn-569f3f.netlify.app',  // ← تأكد من كتابة النطاق بدون أخطاء
        'http://localhost:3000',
        'http://localhost:5173',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Subscription-Status', 'Authorization'],
    'max_age' => 86400,
    'supports_credentials' => false,  // ← ⚠️ غيّر هذا إلى false
];