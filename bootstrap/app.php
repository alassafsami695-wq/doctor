<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        /**
         * ملاحظة: لارافيل 11 يتعامل مع CORS تلقائياً.
         * لا تقم بإضافة HandleCors::class يدوياً هنا لتجنب تكرار الـ Headers.
         */

        // ✅ استثناء مسارات الـ API من فحص CSRF
        // هذا ضروري جداً لعمليات الـ Preflight والـ POST من React/Next.js
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // ✅ تسجيل الأسماء المستعارة للميدل وير الخاص بك
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'is.superadmin'      => \App\Http\Middleware\IsSuperAdmin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();