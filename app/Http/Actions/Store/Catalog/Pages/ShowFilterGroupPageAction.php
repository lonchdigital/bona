<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\DataClasses\ProductStatusDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\FilterGroup;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\FilterGroups\FilterGroupService;
use App\Services\Product\DTO\FilterProductDTO;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use App\Services\ProductCategory\CategoryService;
// use App\Services\WishList\WishListService;
use App\Support\LastModified;

class ShowFilterGroupPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        FilterGroup $filterGroup,
        CatalogFilterRequest $request
    ) {
        $productType->load(['fields', 'fields.options']);

        // get services from service container
        $categoryService = app()->make(CategoryService::class);
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        $countryService = app()->make(CountryService::class);
        $brandService = app()->make(BrandService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);
        //        $wishListService = app()->make(WishListService::class);
        $filerGroupService = app()->make(FilterGroupService::class);

        $groupFilters = $filerGroupService->buildFilterArrayByFilterGroup($filterGroup);
        $requestedPage = $request->toDTO()->filters['page'] ?? null;

        if ($requestedPage !== null) {
            $groupFilters['page'] = $requestedPage;
        }

        $filtersData = new FilterProductDTO($groupFilters);

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType($productType);
        $countries = $countryService->getAvailableCountriesByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType);

        $selectedFiltersOptions = $catalogService->getOptionsByFilterData(
            $productType,
            $filtersData->filters,
            $baseCurrency,
            $colors,
            $countries,
            $brands,
        );

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByTypePaginated(
            $productType,
            $filtersData,
            $filtersData->filters['per_page'] ?? 24,
            $page,
        );

        // TODO:: remove when finish
        //        $wishList = null;
        /*if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }*/

        $productType->meta_tags = $this->handleFollowTag($productType->meta_tags);
        LastModified::set($productType->updated_at);

        return view('pages.store.catalog', [
            'filters' => $catalogService->getFiltersByProductType($productType),
            'filtersData' => $filtersData->filters,
            'productStatuses' => ProductStatusDataClass::getForWeb(),
            'selectedFiltersOptions' => $selectedFiltersOptions,
            'productType' => $productType,
            'categories' => $categoryService->getProductCategories($productType),
            'colors' => $colors,
            'countries' => $countries,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getProductsMaxPrice($productType),
            'faqs' => $productService->getProductTypeFaqs($productType->slug),
            'seoText' => $productService->getProductTypeSeoTextByLanguage($productType->slug, app()->getLocale()),
            //            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'filterGroup' => $filterGroup,
        ]);
    }
}
