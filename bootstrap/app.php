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
        
        // ✅ CORS فقط
        $middleware->prepend([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // ❌ احذف statefulApi() — لا حاجة له مع Bearer Token
        // $middleware->statefulApi();

        // ✅ استثناء API من CSRF (للـ Preflight)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'is.superadmin'      => \App\Http\Middleware\IsSuperAdmin::class,
        ]);
        
        $middleware->appendToGroup('api', [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();