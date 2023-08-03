<?php

namespace App\Http\Actions\Admin\Collections\Pages;

use App\Http\Requests\Admin\Collection\CollectionFilterRequest;
use App\Services\Brand\BrandService;
use App\Services\Collection\CollectionService;

class ShowCollectionsListPageAction
{
    public function __invoke(CollectionFilterRequest $request, CollectionService $service, BrandService $brandService)
    {
        $dto = $request->toDTO();

        return view('pages.admin.collections.list', [
            'collectionsPaginated' => $service->getCollectionsPaginated($dto),
            'brands' => $brandService->getBrands(),
            'search' => $dto->search,
            'searchBrandIds' => $dto->searchBrandIds,
        ]);
    }
}
