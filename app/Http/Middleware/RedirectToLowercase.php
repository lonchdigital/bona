<?php

namespace App\Http\Middleware;

use App\Models\ProductSlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLowercase
{
    /** Query parameters of the former WooCommerce store; the Laravel catalog ignores them. */
    private const LEGACY_QUERY_KEYS = ['filter_style', 'filter_brand', 'orderby', 'add_to_wishlist', ''];

    private const LEGACY_PATHS = [
        '/blog/doborni-planky-ta-nalychnyky' => '/product-category/aksessuar/category/dobir',
        '/nashi-roboty/testoviy' => '/nashi-roboty',
        '/product-category/aksessuar/category/dobir-estet' => '/product-category/aksessuar/category/dobir',
        '/blog/yak-doglyadaty-za-dveryma-comeo' => '/blog/yak-dohlyadaty-za-dveryma-comeo-praktychni-porady',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Compare the decoded path: lowercasing the percent-encoded URL only
        // flipped hex digits of Cyrillic characters (%D0%9C -> %d0%9c), which
        // bounced crawlers between two spellings of the same address.
        $decodedPath = rawurldecode($request->getPathInfo());
        $lowerPath = mb_strtolower($decodedPath);

        if ($lowerPath !== $decodedPath) {
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $lowerPath)));
            $query = $request->getQueryString();

            return redirect($request->getSchemeAndHttpHost().$encodedPath.($query !== null ? '?'.$query : ''), 301);
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

        // Old WooCommerce sorting/filter/wishlist links render the same listing
        // and waste crawl budget; send them to the clean address.
        $query = $request->query();
        $legacyKeys = array_intersect(array_map('strval', array_keys($query)), self::LEGACY_QUERY_KEYS);
        if ($request->isMethod('GET') && ($legacyKeys !== [] || str_contains((string) $request->server('QUERY_STRING'), '?=') || str_starts_with((string) $request->server('QUERY_STRING'), '='))) {
            $clean = array_diff_key($query, array_flip($legacyKeys), ['' => true]);

            return redirect($request->url().($clean ? '?'.http_build_query($clean) : ''), 301);
        }

        return $next($request);
    }
}
