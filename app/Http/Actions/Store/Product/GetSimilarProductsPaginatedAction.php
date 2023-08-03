<?php

namespace App\Http\Actions\Store\Product;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\BaseProductResource;
use App\Models\Product;
use App\Services\Product\SimilarProductsService;

class GetSimilarProductsPaginatedAction extends BaseAction
{
    public function __invoke(Product $product, SimilarProductsService $similarProductsService)
    {
        return BaseProductResource::collection($similarProductsService->getSimilarProductsPaginated($this->getAuthUser(), $product));
    }
}
