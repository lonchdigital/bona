<?php

namespace App\Services\Seo;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\StaticPage;

/**
 * Builds /llms.txt: a plain map telling answer engines which pages matter.
 *
 * Generated from the catalogue rather than written by hand, so a category
 * added tomorrow appears without anybody remembering to edit a file.
 */
class LlmsTxtService
{
    public function build(): string
    {
        $lines = [];

        $lines[] = '# ' . trans('base.organization');
        $lines[] = '';
        $lines[] = '> ' . trans('base.llms_description');
        $lines[] = '';

        $lines[] = '## ' . trans('base.llms_catalogue');
        $lines[] = '';

        foreach (ProductType::orderBy('id')->get() as $productType) {
            $url = route('store.catalog.page', ['productTypeSlug' => $productType->slug]);
            $lines[] = '- [' . $this->clean((string) $productType->name) . '](' . $url . ')';
        }

        // Subcategories and brands are real landing pages people search for.
        // The 1081 products are not listed: llms.txt is a short map an answer
        // engine reads in full, and the sitemap below already holds the lot.
        $categories = Category::with('productType')->orderBy('id')->get();

        if ($categories->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## ' . trans('base.llms_categories');
            $lines[] = '';

            foreach ($categories as $category) {
                if (!$category->productType) {
                    continue;
                }

                $url = route('store.catalog-category.page', [
                    'productTypeSlug' => $category->productType->slug,
                    'categorySlug' => $category->slug,
                ]);

                $lines[] = '- [' . $this->clean((string) $category->name) . '](' . $url . ')';
            }
        }

        $brands = Brand::orderBy('id')->get();

        if ($brands->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## ' . trans('base.llms_brands');
            $lines[] = '';

            foreach ($brands as $brand) {
                $lines[] = '- [' . $this->clean((string) $brand->name) . '](' . route('store.brand.page', ['brandSlug' => $brand->slug]) . ')';
            }
        }

        $lines[] = '';
        $lines[] = '## ' . trans('base.llms_pages');
        $lines[] = '';

        foreach ([
            'store.about-us' => trans('base.about_us'),
            'store.services' => trans('base.services'),
            'store.faq.page' => trans('base.faq'),
            'blog.main.page' => trans('base.blog'),
            'store.works.page' => trans('base.our_works'),
            'store.contacts' => trans('base.contacts'),
        ] as $routeName => $label) {
            if (!app('router')->has($routeName)) {
                continue;
            }

            $lines[] = '- [' . $this->clean($label) . '](' . route($routeName) . ')';
        }

        foreach (StaticPage::all() as $staticPage) {
            $slug = $staticPage->slug ?? null;

            if (!$slug) {
                continue;
            }

            $lines[] = '- [' . $this->clean((string) $staticPage->name) . '](' . route('store.static-page.page', ['staticPageSlug' => $slug]) . ')';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Link labels are markdown, so brackets in a name would break them.
     */
    private function clean(string $value): string
    {
        return trim(str_replace(['[', ']', "\n", "\r"], ' ', $value));
    }
}
