<?php

namespace App\Http\Actions\Admin\Products\Pages;

use App\DataClasses\ProductStatusDataClass;
use App\Models\Product;
use App\Services\Application\ApplicationConfigService;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;

class ShowProductEditPageAction
{
    public function __invoke(
        int                      $productTypeId,
        Product                  $product,
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

        return view('pages.admin.products.edit', [
            'productType' => $productType,
            'product' => $product,
            'productStatuses' => ProductStatusDataClass::get(),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'currencies' => $currencyService->getCurrencies(),
            'categories' => $productCategoryService->getProductCategories($productType),
            'brands' => $brandService->getBrands(),
            'colors' => $colorService->getColors(),
            'countries' => $countryService->getCountries(),
            'subProducts' => $productsService->getSelectedSubItems(json_decode($product->sub_products)),
            'productShortText' => $productsService->getProductShortText($product->id),
            'productText' => $productsService->getProductText($product->id),
            'characteristics' => $productsService->getProductCharacteristics($product->id),
            'productGallery' => $productsService->getProductGallery($product->id),
            'productVideos' => $productsService->getProductVideos($product->id),
            'productFaqs' => $productsService->getProductFaqs($product->id),
            'seoData' => $productsService->getProductSeoText($product->id),
            'attributeOptions' => $productsService->getAttributeOptions($product->id, $productType),
        ]);
    }
}
