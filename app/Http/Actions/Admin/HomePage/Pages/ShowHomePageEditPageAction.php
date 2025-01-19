<?php

namespace App\Http\Actions\Admin\HomePage\Pages;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Services\Admin\ProductField\ProductFieldService;
use App\Services\HomePage\HomePageService;
use App\Services\Product\ProductService;
use App\Services\Admin\ProductType\ProductTypeService;


class ShowHomePageEditPageAction extends BaseAction
{
    public function __invoke(
        ProductFieldService $productFieldService,
        HomePageService $homePageService,
        ProductService $productService,
        ProductTypeService $productTypeService,
    )
    {

        $products = $productService->getLimitedProducts(4)->map(function (Product $product) {
            $product->text = $product->name;
            return $product;
        });


        $wallpaperProductType = ProductType::where('slug', config('domain.wallpaper_product_type_slug'))->first();

//        dd($wallpaperProductType);

        if ($wallpaperProductType) {
            $customFields = $wallpaperProductType->fields()->select(['product_field_id as id', 'field_name'])
                ->where('field_type_id', ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                ->get()
                ->map(function (ProductField $field) {
                    $field->text = $field->field_name;

                    $options = ProductFieldOption::select(['id', 'name'])->where('product_field_id', $field->id)
                        ->get()
                        ->map(function (ProductFieldOption $productFieldOption) {
                            $productFieldOption->text = $productFieldOption->name;
                            return $productFieldOption;
                        });

                    $field->options = $options;

                    return $field;
                });
        } else {
            $customFields = [];
        }


//        dd($homePageService->getHomePageConfig());

        return view('pages.admin.home-page.edit', [
            'products' => $products,
            'fields' => $customFields,
            'allProductTypes' => $productTypeService->getProductTypes(),
            'config' => $homePageService->getHomePageConfig(),
            'selectedNewProducts' => $homePageService->getHomePageNewProducts(),
            'selectedBestSalesProducts' => $homePageService->getHomePageBestSalesProducts(),
            'selectedProductFieldOptions' => $homePageService->getHomePageProductFieldOptions(),
            'slides' => $homePageService->getHomePageSlides(),
            'brands' => $homePageService->getHomePageBrands(),
            'testimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoText(),
        ]);
    }
}
