<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Brand;
use App\Services\Brand\BrandCatalogUrlService;
use Illuminate\Http\RedirectResponse;

class ShowProductByBrandPageAction extends BaseAction
{
    public function __invoke(
        Brand $brand,
        BrandCatalogUrlService $brandCatalogUrlService,
    ): RedirectResponse {
        return redirect($brandCatalogUrlService->storefrontUrl($brand), 301);
    }
}
