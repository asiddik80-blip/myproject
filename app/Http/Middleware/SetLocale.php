<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\App;      // Tambahkan ini
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle($request, Closure $next)
    {
        $locale = Session::get('locale', 'en'); // Default to English
        App::setLocale($locale);

        // Set RTL if the locale is Arabic
        if ($locale == 'ar') {
            view()->share('is_rtl', true);
        } else {
            view()->share('is_rtl', false);
        }

        return $next($request);
    }

    
}
