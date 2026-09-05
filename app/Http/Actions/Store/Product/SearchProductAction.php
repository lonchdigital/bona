<?php

namespace App\Http\Actions\Store\Product;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Product\SearchProductRequest;
use App\Http\Resources\Store\Product\ProductSearchResource;
use App\Services\Search\SearchAnalyticsService;
use App\Services\Search\StorefrontSearchService;

class SearchProductAction extends BaseAction
{
    public function __invoke(
        SearchProductRequest $request,
        StorefrontSearchService $searchService,
        SearchAnalyticsService $analyticsService,
    ) {
        $results = $searchService->search($request->toDTO());
        $analyticsService->record(
            $results['query'],
            $results['product_total'] + $results['service_total']
        );

        return response()->json([
            'data' => [
                'products' => ProductSearchResource::collection($results['products'])->resolve($request),
                'services' => $results['services']->values(),
                'suggestions' => $results['suggestions']->values(),
                'totals' => [
                    'products' => $results['product_total'],
                    'services' => $results['service_total'],
                    'suggestions' => $results['suggestion_total'],
                ],
                'full_results_url' => $results['full_results_url'],
            ],
        ]);
    }
}
