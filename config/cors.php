<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://peppy-faun-569f3f.netlify.app',  // ✅ Frontend production
        'http://localhost:3000',                   // ✅ Local dev (Next.js)
        'http://localhost:5173',                   // ✅ Local dev (Vite)
    ],
    // أو استخدم ['*'] للتجربة فقط (غير آمن للإنتاج)
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Subscription-Status', 'Authorization'],
    'max_age' => 86400,  // ✅ 24 ساعة للـ Preflight cache
    'supports_credentials' => true, // ⚠️ مهم جداً للـ Sanctum
];