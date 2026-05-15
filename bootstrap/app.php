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
        
        // ✅ أولاً: HandlePreflight + HandleCors (قبل أي شيء)
        $middleware->prependToGroup('api', [
            \App\Http\Middleware\HandlePreflight::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // ✅ ثانياً: Sanctum Stateful (بعد CORS)
        $middleware->statefulApi();

        // ✅ استثناء API من CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie',
        ]);

        // ✅ الأسماء المستعارة
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'is.superadmin'      => \App\Http\Middleware\IsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();