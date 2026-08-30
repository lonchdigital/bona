<?php

namespace App\Http\Middleware;

use App\Support\LastModified;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLastModified
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $lastModified = LastModified::get($request);

        if ($lastModified === null || ! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return $response;
        }

        $response->setLastModified($lastModified);
        $response->isNotModified($request);

        return $response;
    }
}
