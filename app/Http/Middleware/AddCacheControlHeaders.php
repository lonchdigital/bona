<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheControlHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set Cache-Control for JS scripts
        //$response->header('Cache-Control', 'public, max-age=86400'); // Здесь 86400 секунд (24 часа)
        $response->header('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}
