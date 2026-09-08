<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Models\ProductType;
use App\Services\Catalog\CatalogColorUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowProductTypeByColorPageAction
{
    public function __invoke(
        ProductType $productType,
        string $color,
        Request $request,
        CatalogColorUrlService $colorUrls,
    ): RedirectResponse {
        $target = $colorUrls->productTypeFilterUrl(
            $productType,
            $colorUrls->findOrFail($color),
        );

        return redirect()->to($this->withQuery($target, $request), 301);
    }

    private function withQuery(string $target, Request $request): string
    {
        $query = $request->getQueryString();

        return $query ? $target.'?'.$query : $target;
    }
}
