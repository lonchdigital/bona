<?php

namespace App\Services\Sitemap;

use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\StaticPage;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class SitemapService extends BaseService
{
    public function generateSitemap(): void
    {
        SitemapGenerator::create(config('app.url'))
            ->getSitemap()
            ->add(Product::all())
            ->add(ProductType::all())
            ->add(Brand::all())
            ->add(Category::all())
            ->add(StaticPage::all())
            ->add(BlogArticle::all())
            ->add(BlogCategory::all())
            ->add(Url::create('/')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.8))

            ->writeToFile(public_path('sitemap.xml'));
    }
}
