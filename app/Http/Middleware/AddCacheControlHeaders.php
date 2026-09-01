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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Dynamic storefront responses remain private and are revalidated,
        // but `no-store` prevents the browser's back/forward cache and makes
        // returning from a product page unnecessarily expensive. Preserve
        // explicit public caching (robots/sitemap) and explicit no-store
        // responses (checkout/comparison) set by their own actions.
        if (! $response->headers->hasCacheControlDirective('public')
            && ! $response->headers->hasCacheControlDirective('no-store')) {
            $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate');
        }

        $response->headers->remove('Pragma');
        $response->headers->remove('Expires');

        return $response;
    }
}
