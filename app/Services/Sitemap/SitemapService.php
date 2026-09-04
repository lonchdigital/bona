<?php

namespace App\Services\Sitemap;

use App\Models\Author;
use App\Models\BlogArticle;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faqs;
use App\Models\HomePageConfig;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ServicesConfig;
use App\Models\ServicesPageSections;
use App\Models\StaticPage;
use App\Models\Work;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService extends BaseService
{
    /**
     * Stamps a url with when the thing behind it last changed.
     *
     * Google reads lastmod to decide what is worth fetching again, and skips
     * it when it is missing rather than guessing — so a record with no
     * timestamp of its own simply goes without one.
     */
    private function withLastModified(Url $url, $changedAt): Url
    {
        if (! $changedAt) {
            return $url;
        }

        return $url->setLastModificationDate(Carbon::parse($changedAt));
    }

    /**
     * Mirrors how the catalogue itself gathers a type's products: either the
     * product names the type outright, or it is attached to it alongside its
     * own. Counting only the first would drop types that are far from empty.
     */
    private function productTypeHasProducts(ProductType $productType): bool
    {
        return Product::query()
            ->where(function ($query) use ($productType) {
                $query->where('product_type_id', $productType->id)
                    ->orWhereHas('productTypes', function ($query) use ($productType) {
                        $query->where('product_types.id', $productType->id);
                    });
            })
            ->exists();
    }

    public function buildSitemap(): Sitemap
    {
        $urls = new Collection;

        // Add homepage
        $homePageChangedAt = HomePageConfig::query()->latest('updated_at')->value('updated_at');

        $urls->push($this->withLastModified(Url::create('/'), $homePageChangedAt));
        $urls->push($this->withLastModified(Url::create('/ru'), $homePageChangedAt));

        // FAQ hub
        $faqChangedAt = Faqs::query()->latest('updated_at')->value('updated_at');

        foreach (['/faq', '/ru/faq'] as $faqUrl) {
            $urls->push($this->withLastModified(Url::create($faqUrl), $faqChangedAt));
        }

        // Add Services
        // Absent until someone fills the services page in — on a fresh
        // install, and in any test that has not seeded it.
        if ($servicesConfig = ServicesConfig::first()) {
            foreach ($servicesConfig->toSitemapTag() as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $servicesConfig->updated_at));
            }
        }

        foreach (ServicesPageSections::query()->whereNotNull('slug')->get() as $service) {
            foreach ($service->toSitemapTag() as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $service->updated_at));
            }
        }

        // Add All Products
        $currentPage = 1;
        do {
            $products = Product::paginate(50, ['*'], 'page', $currentPage);
            foreach ($products as $product) {
                $allLangUrls = $product->toSitemapTag();

                $productImagePath = $product->preview_image_path ?: $product->main_image_path;

                foreach ($allLangUrls as $langUrl) {
                    $url = $this->withLastModified(Url::create($langUrl), $product->updated_at);

                    // Declared against the page it belongs to, which is what
                    // image search expects, rather than as a page of its own.
                    if ($productImagePath) {
                        $url->addImage(url(Storage::url($productImagePath)), (string) $product->name);
                    }

                    $urls->push($url);
                }
            }
            $currentPage++;
        } while ($products->hasMorePages());

        // Categories
        foreach (Category::all() as $category) {
            $allLangUrls = $category->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $category->updated_at));
            }
        }

        // ProductType
        foreach (ProductType::all() as $ProductType) {
            // A type holding nothing still answers 200, with "nothing found"
            // where the grid should be — a soft 404 to offer a crawler.
            if (! $this->productTypeHasProducts($ProductType)) {
                continue;
            }

            $allLangUrls = $ProductType->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $ProductType->updated_at));
            }
        }

        // Brands
        foreach (Brand::all() as $brand) {
            $allLangUrls = $brand->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $brand->updated_at));
            }
        }

        // BlogArticle
        foreach (BlogArticle::all() as $blogArticle) {
            $allLangUrls = $blogArticle->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $blogArticle->updated_at));
            }
        }

        // Works
        $worksChangedAt = Work::published()->max('updated_at');

        foreach (['/nashi-roboty', '/ru/nashi-roboty'] as $worksUrl) {
            $urls->push($this->withLastModified(Url::create($worksUrl), $worksChangedAt));
        }

        foreach (Work::published()->get() as $work) {
            foreach ($work->toSitemapTag() as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $work->updated_at));
            }
        }

        // Author
        foreach (Author::all() as $author) {
            foreach ($author->toSitemapTag() as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $author->updated_at));
            }
        }

        // StaticPage
        foreach (StaticPage::all() as $staticPage) {
            $allLangUrls = $staticPage->toSitemapTag();

            foreach ($allLangUrls as $langUrl) {
                $urls->push($this->withLastModified(Url::create($langUrl), $staticPage->updated_at));
            }
        }

        /*
         * Written from the list above and nothing else. It used to start from
         * SitemapGenerator::getSitemap(), which crawls the site and adds
         * whatever it runs into: that is how 1600 image files and the sign in
         * pages ended up listed as pages in their own right.
         */
        /*
         * RedirectToLowercase sends every request to its lower case form, so
         * an address with a capital in it is never the one a visitor lands
         * on. Listing it here would hand crawlers a 301 to follow instead of
         * the page itself, so the canonical form is what gets written.
         */
        $urls->each(function (Url $url) {
            $url->setUrl(mb_strtolower($url->url));
        });

        $this->addLocalizedAlternates($urls);

        return Sitemap::create()->add($urls->toArray());
    }

    public function generateSitemap(): void
    {
        $this->buildSitemap()->writeToFile(public_path('sitemap.xml'));
    }

    /**
     * Add reciprocal language annotations to every URL for which both the
     * Ukrainian canonical and the /ru version exist. This lets a crawler
     * understand the language cluster even when it discovers a page through
     * the sitemap rather than through the document head.
     */
    private function addLocalizedAlternates(Collection $urls): void
    {
        $groups = $urls->groupBy(function (Url $url) {
            $path = parse_url(url($url->url), PHP_URL_PATH) ?: '/';
            $path = preg_replace('#^/ru(?=/|$)#', '', $path) ?: '/';

            return mb_strtolower(rtrim($path, '/') ?: '/');
        });

        foreach ($groups as $group) {
            $uk = $group->first(function (Url $url) {
                $path = parse_url(url($url->url), PHP_URL_PATH) ?: '/';

                return ! preg_match('#^/ru(?:/|$)#', $path);
            });
            $ru = $group->first(function (Url $url) {
                $path = parse_url(url($url->url), PHP_URL_PATH) ?: '/';

                return (bool) preg_match('#^/ru(?:/|$)#', $path);
            });

            if (! $uk || ! $ru) {
                continue;
            }

            foreach ($group as $url) {
                $url->addAlternate(url($uk->url), 'uk-UA')
                    ->addAlternate(url($ru->url), 'ru-UA')
                    ->addAlternate(url($uk->url), 'x-default');
            }
        }
    }
}
