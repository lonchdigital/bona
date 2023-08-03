<?php

namespace App\Http\Actions\Admin\Brands;

use App\Http\Requests\Admin\Collection\CollectionFilterRequest;
use App\Http\Resources\Admin\Collection\CollectionResource;
use App\Models\Brand;
use App\Services\Collection\CollectionService;

class GetCollectionsByBrandAction
{
    public function __invoke(Brand $brand, CollectionFilterRequest $request, CollectionService $collectionService)
    {
        return CollectionResource::collection($collectionService->searchCollectionsByBrandId($brand->id, $request->toDTO()));
    }
}
