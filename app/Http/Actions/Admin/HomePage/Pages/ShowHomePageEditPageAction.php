<?php

namespace App\Http\Actions\Admin\HomePage\Pages;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Models\Brand;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Services\Admin\ProductField\ProductFieldService;
use App\Services\Brand\BrandService;
use App\Services\HomePage\HomePageService;

class ShowHomePageEditPageAction extends BaseAction
{
    public function __invoke(
        BrandService $brandService,
        ProductFieldService $productFieldService,
        HomePageService $homePageService,
    )
    {
        $brands = $brandService->getBrands()->map(function (Brand $brand) {
           $brand->text = $brand->name;
           return $brand;
        });

        $wallpaperProductType = ProductType::where('slug', config('domain.wallpaper_product_type_slug'))->first();

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


        return view('pages.admin.home-page.edit', [
            'brands' => $brands,
            'fields' => $customFields,
            'config' => $homePageService->getHomePageConfig(),
            'selectedBrands' => $homePageService->getHomePageBrands(),
            'selectedProductFieldOptions' => $homePageService->getHomePageProductFieldOptions(),
            'slides' => $homePageService->getHomePageSlides(),
        ]);
    }
}
