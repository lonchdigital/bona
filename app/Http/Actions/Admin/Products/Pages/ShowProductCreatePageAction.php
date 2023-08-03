<?php

namespace App\Http\Actions\Admin\Products\Pages;

use App\DataClasses\ProductStatusDataClass;
use App\Services\Application\ApplicationConfigService;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;

class ShowProductCreatePageAction
{
    public function __invoke(
        int                      $productTypeId,
        ProductService           $productsService,
        ApplicationConfigService $applicationService,
        CurrencyService          $currencyService,
        CategoryService          $productCategoryService,
        BrandService             $brandService,
        ColorService             $colorService,
        CountryService           $countryService,
    )
    {
        $productType = $productsService->getProductTypeWithFields($productTypeId);

        if (!$productType) {
            abort(404);
        }

        $currencies = $currencyService->getCurrencies();

        return view('pages.admin.products.edit', [
            'productType' => $productType,
            'productStatuses' => ProductStatusDataClass::get(),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'currencies' => $currencies,
            'categories' => $productCategoryService->getProductCategories($productType),
            'brands' => $brandService->getBrands(),
            'colors' => $colorService->getColors(),
            'countries' => $countryService->getCountries(),
        ]);
    }
}
