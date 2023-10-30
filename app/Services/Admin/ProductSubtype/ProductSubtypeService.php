<?php

namespace App\Services\Admin\ProductSubtype;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\DataClasses\ProductSizeTypesDataClass;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType; // I have to remove it
use App\Models\ProductSubtype;
use App\Models\ProductTypeSizeOption;
use App\Services\Admin\ProductSubtype\DTO\EditProductSubtypeDTO;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use App\Services\Base\ServiceActionResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductSubtypeService extends BaseService
{
    public const PRODUCT_TYPE_IMAGES_FOLDER = 'product-type-images';

    public function getProductSubtypes(): Collection
    {
        return ProductSubtype::get();
    }

    public function getProductTypesWithAllData(): Collection
    {
        $productTypes = ProductType::get();

        $productTypeSizeOptions = ProductTypeSizeOption::get();

        $productTypes->map(function (ProductType $productType) use($productTypeSizeOptions) {
            $productType->categories = Category::select()->where('product_type_id', $productType->id)->get();
            $productType->length_options = $productTypeSizeOptions->where('type', 'LENGTH')->where('product_type_id', $productType->id) ?? null;
            $productType->width_options = $productTypeSizeOptions->where('type', 'WIDTH')->where('product_type_id', $productType->id) ?? null;
            $productType->height_options = $productTypeSizeOptions->where('type', 'HEIGHT')->where('product_type_id', $productType->id) ?? null;
            return $productType;
        });

        return $productTypes;
    }

    public function getProductSubtypesPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductSubtype::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getProductTypesWithCategoriesPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductType::with('creator')
            ->where('has_category', true)
            ->paginate(config('domain.items_per_page'));
    }

    public function createProductSubtype(EditProductSubtypeDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithDBTransaction(function () use($request, $creator) {

            $productType = ProductSubtype::create([
                'creator_id' => $creator->id,
                'name' => $request->productSubtypeName,
                'slug' => $request->slug,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_subtype_create_success'));
        });
    }

    public function updateProductType(ProductSubtype $productSubtype, EditProductSubtypeDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productSubtype, $request) {

            $productSubtype->update([
                'name' => $request->productSubtypeName,
                'slug' => $request->slug,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_subtype_edit_success'));
        });
    }

    public function deleteProductSubtype(ProductSubtype $productSubtype): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productSubtype) {

            /*if (Product::where('product_type_id', $productSubtype->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_type_in_use'));
            } else {
                $productSubtype->delete();

                return ServiceActionResult::make(true, trans('admin.product_type_delete_success'));
            }*/

            $productSubtype->delete();

            return ServiceActionResult::make(true, 'Need a check !');
        });
    }

}
