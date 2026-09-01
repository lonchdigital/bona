<?php

namespace App\Http\Actions\Store\Comparison\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductComparisonService;
use Illuminate\Http\Request;

class ShowComparisonPageAction extends BaseAction
{
    public function __invoke(
        Request $request,
        ProductComparisonService $comparisonService,
        CurrencyService $currencyService,
    ) {
        $slugs = $comparisonService->parseSlugs($request->query('products'));
        $products = $comparisonService->getProducts($slugs);

        return response()
            ->view('pages.store.comparison', [
                'products' => $products,
                'comparisonRows' => $comparisonService->buildRows($products),
                'baseCurrency' => $currencyService->getBaseCurrency(),
                'hasProductsQuery' => $request->has('products'),
                'maxProducts' => ProductComparisonService::MAX_PRODUCTS,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store');
    }
}
