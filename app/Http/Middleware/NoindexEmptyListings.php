<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * A catalog listing without products (an empty category, an over-filtered
 * result or a page past the last one) still answers 200 so visitors and old
 * links keep working, but it must not be indexed as a thin page.
 */
class NoindexEmptyListings
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        $view = $response->original ?? null;
        if (! $view instanceof View) {
            return $response;
        }

        $paginator = $view->getData()['productsPaginated'] ?? null;
        if (! $paginator instanceof LengthAwarePaginator) {
            return $response;
        }

        $isEmpty = $paginator->total() === 0 || $paginator->currentPage() > max(1, $paginator->lastPage());
        if (! $isEmpty) {
            return $response;
        }

        $response->headers->set('X-Robots-Tag', 'noindex, follow');

        $content = $response->getContent();
        if (is_string($content)) {
            $content = preg_replace(
                '/<meta\s+name="robots"\s+content="[^"]*"\s*\/?>/i',
                '<meta name="robots" content="noindex, follow">',
                $content,
                -1,
                $replaced,
            );

            if ($replaced === 0) {
                $content = preg_replace('/<\/head>/i', '<meta name="robots" content="noindex, follow"></head>', (string) $content, 1);
            }

            $response->setContent((string) $content);
        }

        return $response;
    }
}
