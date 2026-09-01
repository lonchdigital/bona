<?php

namespace App\Http\Middleware;

use App\Support\LastModified;
use Carbon\CarbonImmutable;
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

        // A page's HTML also references hashed Vite assets. Content can stay
        // unchanged in the database while a deployment changes those hashes,
        // so the build manifest must participate in HTTP revalidation. Without
        // this, a browser can receive a 304 and keep HTML that points at assets
        // removed with the previous release.
        $manifestTimestamp = @filemtime(public_path('build/manifest.json'));

        if ($manifestTimestamp !== false && $manifestTimestamp > $lastModified->getTimestamp()) {
            $lastModified = CarbonImmutable::createFromTimestampUTC($manifestTimestamp);
        }

        $response->setLastModified($lastModified);
        $response->isNotModified($request);

        return $response;
    }
}
