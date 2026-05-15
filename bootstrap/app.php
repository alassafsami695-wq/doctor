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
        
        // ✅ أضف HandleCors يدوياً في المقدمة
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        // ✅ استثناء مسارات الـ API من فحص CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'login',
            'register',
            'logout',
        ]);

        // ✅ تسجيل الأسماء المستعارة للميدل وير
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'is.superadmin'      => \App\Http\Middleware\IsSuperAdmin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();