<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapService;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';

    protected $description = 'Generates a sitemap';

    public function handle(SitemapService $sitemapService): void
    {
        $sitemapService->generateSitemap();
    }
}
