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
        // 1. تفعيل التعامل مع الجلسات وحالة الاتصال للـ API
        $middleware->statefulApi();

        // 2. حل مشكلة الخطأ 419: استثناء مسارات الـ API من حماية CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*', 
        ]);

        // 3. تعريف الأسماء المستعارة للوسائط (Middlewares)
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'is.superadmin'      => \App\Http\Middleware\IsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();