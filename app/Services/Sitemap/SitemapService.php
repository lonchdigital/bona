<?php

namespace App\Services\Sitemap;

use App\Models\BlogArticle;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ServicesConfig;
use App\Models\ProductType;
use App\Models\StaticPage;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class SitemapService extends BaseService
{
    public function generateSitemap(): void
    {
        $urls = new Collection();

        // Add homepage
        $urls->push(Url::create('/')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1)
        );
        $urls->push(Url::create('/ru')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1)
        );

        // Add Services
        $allLangUrls = ServicesConfig::first()->toSitemapTag();
        foreach ($allLangUrls as $langUrl) {
            $urls->push(Url::create($langUrl)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1)
            );
        }


        // Add All Products
        $currentPage = 1;
        do {
            $products = Product::paginate(50, ['*'], 'page', $currentPage);
            foreach ($products as $product) {
                $allLangUrls = $product->toSitemapTag();

                foreach ($allLangUrls as $langUrl) {
                    $urls->push(Url::create($langUrl)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                    );
                }
            }
            $currentPage++;
        } while ($products->hasMorePages());

        // Categories
        foreach (Category::all() as $category) {
            $allLangUrls = $category->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push(Url::create($langUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
                );
            }
        }

        // ProductType
        foreach (ProductType::all() as $ProductType) {
            $allLangUrls = $ProductType->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push(Url::create($langUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
                );
            }
        }

        // Brands
        foreach (Brand::all() as $brand) {
            $allLangUrls = $brand->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push(Url::create($langUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
                );
            }
        }

        // BlogArticle
        foreach (BlogArticle::all() as $blogArticle) {
            $allLangUrls = $blogArticle->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push(Url::create($langUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
                );
            }
        }

        // StaticPage
        foreach (StaticPage::all() as $staticPage) {
            $allLangUrls = $staticPage->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push(Url::create($langUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
                );
            }
        }

        // create SitemapGenerator with all urls
        SitemapGenerator::create(config('app.url'))
            ->getSitemap()
            ->add($urls->toArray())
            ->writeToFile(public_path('sitemap.xml'));
    }
}
