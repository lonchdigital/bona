<?php

namespace App\Http\Actions\Store\Brand\Pages;

use App\Helpers\MultiLangRoute;
use App\Http\Actions\Admin\BaseAction;
use App\Models\Brand;
use App\Services\Brand\BrandCatalogUrlService;

class ShowBrandsListPageAction extends BaseAction
{
    public function __invoke(?string $letter = null)
    {
        // A handful of manufacturers does not need an alphabet: the letter
        // pages were near-identical copies that Google folded together.
        if ($letter !== 'all') {
            return redirect(MultiLangRoute::getMultiLangRoute('store.brands.list.page', ['letter' => 'all']), 301);
        }

        $urls = app(BrandCatalogUrlService::class);

        $brands = Brand::query()->get()
            ->map(fn (Brand $brand) => ['brand' => $brand, 'type' => $urls->preferredProductType($brand)])
            ->filter(fn (array $item) => $item['type'] !== null)
            ->sortBy(fn (array $item) => mb_strtolower((string) $item['brand']->name))
            ->map(fn (array $item) => [
                'name' => (string) $item['brand']->name,
                'logo' => $item['brand']->logo_image_url,
                'type' => (string) $item['type']->name,
                'url' => $urls->storefrontUrl($item['brand']),
            ])
            ->values();

        return view('pages.store.brands', ['brands' => $brands]);
    }
}
