<?php

namespace App\Services\Seogen;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SeoGenConfig;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Seogen\DTO\EditSeogenDTO;
use Illuminate\Support\Collection;

class SeogenService extends BaseService
{
    public function getSeogen(): Collection
    {
        return SeoGenConfig::get();
    }

    public function editSeogen(EditSeogenDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            SeoGenConfig::query()->delete();

            foreach ($request->productCategory as $productCategory) {
                SeoGenConfig::create([
                    'page_type' => SeoGenConfig::PAGE_TYPE_PRODUCT_CATEGORY,
                    'product_type_id' => $productCategory['product_type_id'],
                    'html_title_tag' => $productCategory['title_tag'],
                    'html_h1_tag' => $productCategory['h1_tag'],
                    'meta_title_tag' => $productCategory['meta_title_tag'],
                    'meta_description_tag' => $productCategory['meta_description_tag'],
                    'meta_keywords_tag' => $productCategory['meta_keywords_tag'],
                ]);
            }

            foreach ($request->product as $product) {
                SeoGenConfig::create([
                    'page_type' => SeoGenConfig::PAGE_TYPE_PRODUCT,
                    'product_type_id' => $product['product_type_id'],
                    'html_title_tag' => $product['title_tag'],
                    'html_h1_tag' => $product['h1_tag'],
                    'meta_title_tag' => $product['meta_title_tag'],
                    'meta_description_tag' => $product['meta_description_tag'],
                    'meta_keywords_tag' => $product['meta_keywords_tag'],
                ]);
            }

            SeoGenConfig::create([
                'page_type' => SeoGenConfig::PAGE_TYPE_BRAND,
                'product_type_id' => null,
                'html_title_tag' => $request->brandTitleTag,
                'html_h1_tag' => $request->brandH1Tag,
                'meta_title_tag' => $request->brandMetaTitleTag,
                'meta_description_tag' => $request->brandMetaDescriptionTag,
                'meta_keywords_tag' => $request->brandMetaKeywordsTag,
            ]);

            return ServiceActionResult::make(true, trans('admin.seogen_edit_success'));
        });
    }

    public function getTagsForCategories(ProductType $productType, Category $category): ?SeoGenConfig
    {
        $seogenData = SeoGenConfig::where('page_type', SeoGenConfig::PAGE_TYPE_PRODUCT_CATEGORY)->where('product_type_id', $productType->id)->first();

        if($seogenData) {
            $seogenData->html_title_tag = $this->replaceDataOnCategoryString($seogenData->html_title_tag, $category);
            $seogenData->html_h1_tag = $this->replaceDataOnCategoryString($seogenData->html_h1_tag, $category);
            $seogenData->meta_title_tag = $this->replaceDataOnCategoryString($seogenData->meta_title_tag, $category);
            $seogenData->meta_description_tag = $this->replaceDataOnCategoryString($seogenData->meta_description_tag, $category);
            $seogenData->meta_keywords_tag = $this->replaceDataOnCategoryString($seogenData->meta_keywords_tag, $category);
        }

        return $seogenData;
    }

    private function replaceDataOnCategoryString(string $originalString, Category $category): string
    {
        $originalString = str_replace('[category_id]', $category->id, $originalString);
        $originalString = str_replace('[category_name]', $category->name, $originalString);
        $originalString = str_replace('[count_of_products_in_category]', $category->count_of_products, $originalString);

        return $originalString;
    }

    public function getTagsForProducts(ProductType $productType, Product $product): ?SeoGenConfig
    {
        $seogenData = SeoGenConfig::where('page_type', SeoGenConfig::PAGE_TYPE_PRODUCT)->where('product_type_id', $productType->id)->first();

        if($seogenData) {
            $seogenData->html_title_tag = $this->replaceDataOnProductString($seogenData->html_title_tag, $product);
            $seogenData->html_h1_tag = $this->replaceDataOnProductString($seogenData->html_h1_tag, $product);
            $seogenData->meta_title_tag = $this->replaceDataOnProductString($seogenData->meta_title_tag, $product);
            $seogenData->meta_description_tag = $this->replaceDataOnProductString($seogenData->meta_description_tag, $product);
            $seogenData->meta_keywords_tag = $this->replaceDataOnProductString($seogenData->meta_keywords_tag, $product);
        }

        return $seogenData;
    }

    private function replaceDataOnProductString(string $originalString, Product $product): string
    {
        $originalString = str_replace('[product_id]', $product->id, $originalString);
        $originalString = str_replace('[product_name]', $product->name, $originalString);
        $originalString = str_replace('[sku]', $product->sku, $originalString);
        $originalString = str_replace('[price]', $product->price, $originalString);
        $originalString = str_replace('[brand_name]', $product->brand->name, $originalString);

        return $originalString;
    }

    public function getTagsForBrands(Brand $brand): ?SeoGenConfig
    {
        $seogenData = SeoGenConfig::where('page_type', SeoGenConfig::PAGE_TYPE_BRAND)->first();

        if($seogenData) {
            $seogenData->html_title_tag = $this->replaceDataOnBrandString($seogenData->html_title_tag, $brand);
            $seogenData->html_h1_tag = $this->replaceDataOnBrandString($seogenData->html_h1_tag, $brand);
            $seogenData->meta_title_tag = $this->replaceDataOnBrandString($seogenData->meta_title_tag, $brand);
            $seogenData->meta_description_tag = $this->replaceDataOnBrandString($seogenData->meta_description_tag, $brand);
            $seogenData->meta_keywords_tag = $this->replaceDataOnBrandString($seogenData->meta_keywords_tag, $brand);
        }

        return $seogenData;
    }

    private function replaceDataOnBrandString(string $originalString, Brand $brand): string
    {
        $originalString = str_replace('[brand_id]', $brand->id, $originalString);
        $originalString = str_replace('[brand_name]', $brand->name, $originalString);

        return $originalString;
    }
}
