<?php

namespace App\Services\Admin\ProductAttribute;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Models\Product;
use App\Models\ProductCustomField;
use App\Services\Base\ServiceActionResult;
use Carbon\Carbon;
use App\Models\ProductAttribute;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Services\Admin\ProductAttribute\DTO\EditProductAttributeDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductAttributeService extends BaseService
{
    public const OPTION_IMAGES_FOLDER = 'field-option-images';

    public function getProductAttributes(): Collection
    {
        return ProductAttribute::get();
    }
    public function getProductAttributesPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductAttribute::paginate(config('domain.items_per_page'));
    }

    public function createProductAttribute(EditProductAttributeDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {
            ProductAttribute::create([
                'attribute_name' => $request->productAttributeName,
                'slug' => $request->slug,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_field_create_success'));
        });
    }

    public function updateProductField(ProductAttribute $productAttribute, EditProductAttributeDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productAttribute, $request) {
            $productAttribute->update([
                'attribute_name' => $request->productAttributeName,
                'slug' => $request->slug,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_field_edit_success'));
        });
    }


    public function deleteProductAttribute(ProductAttribute $productAttribute): ServiceActionResult
    {

        if (count($productAttribute->types)) {
            return ServiceActionResult::make(false, trans('admin.product_field_in_use'));
        }

        return $this->coverWithDBTransaction(function () use ($productAttribute) {

//            $productField->fieldFilterOptions()->delete();
            $productAttribute->productAttributeOptions()->delete();
            $productAttribute->delete();

            return ServiceActionResult::make(true, trans('admin.product_field_delete_success'));
        });
    }

    public function getOptionsInUse(ProductField $productField): array
    {
        if ($productField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
            $allOptions = $productField->options->pluck('id');

            $existingOptions = [];

            foreach ($allOptions as $option) {
                if (Product::whereRaw('JSON_EXTRACT(custom_fields, ?) is not null')->addBinding('$."' . $option .'"')->exists()) {
                    $existingOptions[] = $option;
                }
            }

            return $existingOptions;
        }

        return [];
    }
}
