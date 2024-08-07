<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use Abordage\LastModified\Facades\LastModified;
use App\DataClasses\ProductStatusDataClass;

class ShowCatalogPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request
    )
    {
        $productType->load(['fields', 'fields.options']);

        /* TODO:: remove when finish */
        //get services from service container
//        $categoryService = app()->make(CategoryService::class);
//        $countryService = app()->make(CountryService::class);
//        $wishListService = app()->make(WishListService::class);
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        $brandService = app()->make(BrandService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);


        $filtersData = $request->toDTO();

//        $countries = $countryService->getAvailableCountriesByProductType($productType);
        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType);
        $brandsSortedByFirstLetter = $brandService->sortBrandsByFirstLetterByProductType($brands);


        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByTypePaginated(
            $productType,
            $filtersData,
            $filtersData->filters['per_page'] ?? 18,
            $page,
        );

        $template = 'pages.store.catalog';
        if( $productType->id == config('constants.ROZSUVNI_DVERI_ID') ) {
            $template = 'pages.store.catalog-variety.rozsuvni-dveri-catalog';
        }

        $productType->meta_tags = $this->handleFollowTag($productType->meta_tags);
        LastModified::set($productType->updated_at);


        $allFields = $productType->fields;
        $allFields->map(function ($field) use ($productType) {
            $field->options = $field->optionsWithProducts($productType);
            return $field;
        });


        return view($template, [
            'filters' => $catalogService->getFiltersByProductType($productType),
            'filtersData' => $filtersData->filters,
            'productStatuses' => ProductStatusDataClass::getForWeb(),
//            'selectedFiltersOptions' => $selectedFiltersOptions,
            'productType' => $productType,
            'colors' => $colors,
            'brandsSortedByFirstLetter' => $brandsSortedByFirstLetter,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getProductsMaxPrice($productType),
            'faqs' => $productService->getProductTypeFaqs($productType->slug),
            'seoText' => $productService->getProductTypeSeoTextByLanguage($productType->slug, app()->getLocale())
        ]);
    }
}
