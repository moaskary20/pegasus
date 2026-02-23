<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);
        // استثناء مسارات API من التحقق من CSRF (تطبيق الموبايل يستخدم Bearer token وليس الجلسة)
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/auth/login',
            'api/auth/register',
            'api/auth/logout',
            'api/auth/user',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
