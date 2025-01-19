<?php

namespace App\Http\Actions\Admin\Brands;

use App\Http\Requests\Store\Brand\BrandSearchRequest;
use App\Http\Resources\Admin\Product\ProductSearchResource;
use App\Services\Brand\BrandService;

class GetAllBrandsBySearchAction
{
    public function __invoke(BrandSearchRequest $request, BrandService $brandService,)
    {
        return ProductSearchResource::collection($brandService->searchBrandsByName($request->toDTO()));
    }
}
