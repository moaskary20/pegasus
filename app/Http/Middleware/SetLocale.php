<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Set the application locale from session (web).
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (is_string($locale) && in_array($locale, ['ar', 'en'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

