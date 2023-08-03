<?php

namespace App\Http\Actions\Admin\Products\Pages;

use App\Http\Requests\Admin\Product\ProductFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Collection\CollectionService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Product\ProductService;

class ShowProductsListPageAction
{
    public function __invoke(
        ProductFilterRequest $request,
        ProductType          $productType,
        ProductService       $service,
        BrandService         $brandService,
        ColorService         $colorService,
        CollectionService    $collectionService,
        CountryService       $countryService,
    )
    {
        $dto = $request->toDTO();

        $productsPaginated = $service->getProductsByTypePaginatedAdmin($productType->id, $request->toDTO());

        return view('pages.admin.products.list', [
            'productType' => $productType,
            'productsPaginated' => $productsPaginated,
            'brands' => $brandService->getBrands(),
            'colors' => $colorService->getColors(),
            'collections' => $collectionService->getCollections(),
            'countries' => $countryService->getCountries(),
            'searchData' => $dto,
        ]);
    }
}
