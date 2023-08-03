<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = config('app.fallback_locale');
        if (auth()->user()) {
            $lang = auth()->user()->language;
        }

        app()->setLocale($lang);

        \URL::defaults(['lang' => $lang]);

        return $next($request);
    }
}
