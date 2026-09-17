<?php

namespace App\Http\Middleware;

use App\Models\ProductSlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLowercase
{
    private const LEGACY_PATHS = [
        '/blog/doborni-planky-ta-nalychnyky' => '/product-category/aksessuar/category/dobir',
        '/nashi-roboty/testoviy' => '/nashi-roboty',
        '/product-category/aksessuar/category/dobir-estet' => '/product-category/aksessuar/category/dobir',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
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

        // Addresses Google still requests that no longer exist (Search Console 404 report).
        $path = '/'.ltrim($request->path(), '/');
        $prefix = preg_match('#^/ru(?=/)#', $path) ? '/ru' : '';
        $unprefixed = substr($path, strlen($prefix));
        $legacyTarget = self::LEGACY_PATHS[$unprefixed] ?? null;

        if ($legacyTarget === null && preg_match('#^/product/([^/]+)/similar$#', $unprefixed, $matches)) {
            // Resolve renamed products here as well, so the old endpoint needs a single hop.
            $legacyTarget = ProductSlugRedirect::targetPathFor($matches[1]) ?? '/product/'.$matches[1];
        }

        if ($legacyTarget !== null) {
            return redirect($prefix.$legacyTarget, 301);
        }

        return $next($request);
    }
}
