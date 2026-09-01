<?php

namespace App\Http\Actions\Store\Seo;

use App\Services\Sitemap\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class ShowSitemapXml
{
    public function __invoke(SitemapService $sitemapService): Response
    {
        $xml = Cache::remember(
            'seo.sitemap.xml.v2',
            now()->addHours(6),
            fn () => $sitemapService->buildSitemap()->render(),
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600, s-maxage=3600',
        ]);
    }
}
