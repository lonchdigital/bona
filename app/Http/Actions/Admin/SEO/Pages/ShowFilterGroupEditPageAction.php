<?php

namespace App\Http\Actions\Admin\SEO\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Admin\Collection\CollectionResource;
use App\Http\Resources\Admin\SEO\ListResource;
use App\Http\Resources\Admin\SEO\ProductTypeWithFilters;
use App\Models\FilterGroup;
use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Brand\BrandService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;

class ShowFilterGroupEditPageAction extends BaseAction
{
    public function __invoke(
        FilterGroup $filterGroup,
        ProductTypeService $productTypeService,
        BrandService $brandService,
        CountryService $countryService,
        ColorService $colorService,
    )
    {
        return view('pages.admin.seo_fields.filter-groups', [
            'filterGroup' => $filterGroup,
            'brands' => ListResource::collection($brandService->getBrands()),
            'countries' => CollectionResource::collection($countryService->getCountries()),
            'colors' => ListResource::collection($colorService->getColors()),
            'productTypes' => ProductTypeWithFilters::collection($productTypeService->getProductTypesWithAllData()),

        ]);
    }
}
