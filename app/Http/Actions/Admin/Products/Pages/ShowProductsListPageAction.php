<?php

namespace App\Http\Actions\Admin\Products\Pages;

use App\Http\Requests\Admin\Product\ProductFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Product\ProductService;
use App\Services\ProductCategory\CategoryService;

class ShowProductsListPageAction
{
    public function __invoke(
        ProductFilterRequest $request,
        ProductType          $productType,
        ProductService       $service,
        BrandService         $brandService,
        ColorService         $colorService,
        CountryService       $countryService,
        CategoryService      $categoryService,
    )
    {
        $dto = $request->toDTO();

        $productsPaginated = $service->getProductsByTypePaginatedAdmin($productType->id, $request->toDTO());

        return view('pages.admin.products.list', [
            'productType' => $productType,
            'productsPaginated' => $productsPaginated,
            'brands' => $brandService->getBrands(),
            'colors' => $colorService->getColors(),
            'countries' => $countryService->getCountries(),
            'categories' => $categoryService->getProductCategories($productType),
            'searchData' => $dto,
        ]);
    }
}
