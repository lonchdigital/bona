<?php

namespace App\Jobs;

use App\Services\Sitemap\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RegenerateSitemapJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SitemapService $sitemapService): void
    {
        \Log::info('Generating sitemap.xml start.');
        Cache::forget('seo.sitemap.xml.v2');
        $sitemapService->generateSitemap();
        \Log::info('Generating sitemap.xml success.');
    }
}
