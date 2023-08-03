<?php

namespace App\Jobs;

use App\Services\Sitemap\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegenerateSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SitemapService $sitemapService): void
    {
        \Log::info('Generating sitemap.xml start.');
        $sitemapService->generateSitemap();
        \Log::info('Generating sitemap.xml success.');
    }
}
