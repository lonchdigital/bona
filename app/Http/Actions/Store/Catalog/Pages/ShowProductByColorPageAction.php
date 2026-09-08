<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Services\Catalog\CatalogColorUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowProductByColorPageAction
{
    public function __invoke(
        string $color,
        Request $request,
        CatalogColorUrlService $colorUrls,
    ): RedirectResponse {
        $target = $colorUrls->allProductsFilterUrl($colorUrls->findOrFail($color));

        return redirect()->to($this->withQuery($target, $request), 301);
    }

    private function withQuery(string $target, Request $request): string
    {
        $query = $request->getQueryString();

        return $query ? $target.'?'.$query : $target;
    }
}
