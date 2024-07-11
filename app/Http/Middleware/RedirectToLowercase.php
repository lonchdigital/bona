<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLowercase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $url = strtolower($request->url());

        if ($url !== $request->url()) {
            return redirect($url, 301);
        }

        // Specific redirect rule
        if ($request->is('product-category/dverni-rucky')) {
            return redirect('/product-category/aksessuar/category/dverni-rucky', 301);
        }

        return $next($request);
    }
}
