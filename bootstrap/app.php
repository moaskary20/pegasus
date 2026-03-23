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
        // توجيه غير المسجّلين: لوحة التحكم -> /admin/login، الموقع العام -> صفحة تسجيل الدخول
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return url('/admin/login');
            }
            // طلبات Livewire القادمة من لوحة التحكم (Referer يحتوي admin)
            if (str_contains((string) $request->header('Referer', ''), '/admin')) {
                return url('/admin/login');
            }

            return route('site.auth');
        });
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
