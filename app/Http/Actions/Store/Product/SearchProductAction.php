<?php

namespace App\Http\Actions\Store\Product;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Product\SearchProductRequest;
use App\Http\Resources\Store\Product\ProductSearchResource;
use App\Services\Search\StorefrontSearchService;

class SearchProductAction extends BaseAction
{
    public function __invoke(SearchProductRequest $request, StorefrontSearchService $searchService)
    {
        $results = $searchService->search($request->toDTO());

        return response()->json([
            'data' => [
                'products' => ProductSearchResource::collection($results['products'])->resolve($request),
                'services' => $results['services']->values(),
            ],
        ]);
    }
}
