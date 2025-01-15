<?php

namespace App\Services\Admin\ProductType;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\DataClasses\ProductSizeTypesDataClass;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductTypeSizeOption;
use App\Models\SeoText;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use App\Services\Base\ServiceActionResult;
use App\Services\Admin\ProductType\DTO\EditProductTypeDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ProductTypeService extends BaseService
{
    public const PRODUCT_TYPE_IMAGES_FOLDER = 'product-type-images';

    public function getProductTypes(): Collection
    {
        return ProductType::get();
    }

    public function getSortedProductTypes(): Collection
    {
        return ProductType::where('sort_order', '>', 0)->orderBy('sort_order')->get();
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

    public function getProductTypesPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductType::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getProductTypesWithCategoriesPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductType::with('creator')
            ->where('has_category', true)
            ->paginate(config('domain.items_per_page'));
    }

    public function createProductType(EditProductTypeDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithDBTransaction(function () use($request, $creator) {

            $path = self::PRODUCT_TYPE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

            if( !is_null($request->image) ) {
                $this->storeImage($path, $request->image, 'webp');
                $this->storeImage($path, $request->image, 'jpg');
            }

            $productType = ProductType::create([
                'creator_id' => $creator->id,
                'name' => $request->productTypeName,
                'slug' => $request->slug,
                'product_point_name' => $request->pointName,
                'image_path' => $path . '.webp',

                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,

                'meta_product_title' => $request->metaProductTitle,
                'meta_product_description' => $request->metaProductDescription,

                'has_brand' => $request->productTypeHasBrand,
                'has_color' => $request->productTypeHasColor,
                'has_collection' => $request->productTypeHasCollection,
                'has_category' => $request->productTypeHasCategory,
                'has_size' => $request->productTypeHasSize,

                'has_length' => $request->productTypeHasLength,
                'filter_by_length' => $request->productTypeFilterByLength,
                'product_size_length_filter_type_id' => $request->productTypeFilterByLengthFilterTypeId,
                'product_size_length_show_on_main_filter' => $request->productTypeFilterByLengthShowOnMainFilter,
                'product_size_length_filter_full_position_id' => $request->productTypeFilterByLengthFilterFullPositionId,
                'product_size_length_filter_name' => $request->productTypeFilterByLengthName,

                'has_width' => $request->productTypeHasWidth,
                'filter_by_width' => $request->productTypeFilterByWidth,
                'product_size_width_filter_type_id' => $request->productTypeFilterByWidthFilterTypeId,
                'product_size_width_show_on_main_filter' => $request->productTypeFilterByWidthShowOnMainFilter,
                'product_size_width_filter_full_position_id' => $request->productTypeFilterByWidthFilterFullPositionId,
                'product_size_width_filter_name' => $request->productTypeFilterByWidthName,

                'has_height' => $request->productTypeHasHeight,
                'filter_by_height' => $request->productTypeFilterByHeight,
                'product_size_height_filter_type_id' => $request->productTypeFilterByHeightFilterTypeId,
                'product_size_height_show_on_main_filter' => $request->productTypeFilterByHeightShowOnMainFilter,
                'product_size_height_filter_full_position_id' => $request->productTypeFilterByHeightFilterFullPositionId,
                'product_size_height_filter_name' => $request->productTypeFilterByHeightName,

                'size_points' => $request->productSizePoints,
            ]);

            $this->createSizeFilterOptions($productType, $request);

            if ($request->productTypeFields) {
                $productType->fields()->sync($this->prepareProductFieldsToSync($request->productTypeFields));
            } else {
                $productType->fields()->sync([]);
            }

            if( !is_null($request->faqs) ) {
                $this->syncFaqs($productType->slug, $request->faqs);
            }

            SeoText::updateSeoText($productType->slug, $request->seoTitle, $request->seoText);

            return ServiceActionResult::make(true, trans('admin.product_type_create_success'));
        });
    }

    public function updateProductType(ProductType $productType, EditProductTypeDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $request) {

            $dataToUpdate = [
                'name' => $request->productTypeName,
                'slug' => $request->slug,
                'product_point_name' => $request->pointName,

                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,

                'meta_product_title' => $request->metaProductTitle,
                'meta_product_description' => $request->metaProductDescription,

                'has_brand' => $request->productTypeHasBrand,
                'has_color' => $request->productTypeHasColor,
                'has_collection' => $request->productTypeHasCollection,
                'has_category' => $request->productTypeHasCategory,
                'has_size' => $request->productTypeHasSize,

                'has_length' => $request->productTypeHasLength,
                'filter_by_length' => $request->productTypeFilterByLength,
                'product_size_length_filter_type_id' => $request->productTypeFilterByLengthFilterTypeId,
                'product_size_length_show_on_main_filter' => $request->productTypeFilterByLengthShowOnMainFilter,
                'product_size_length_filter_full_position_id' => $request->productTypeFilterByLengthFilterFullPositionId,
                'product_size_length_filter_name' => $request->productTypeFilterByLengthName,

                'has_width' => $request->productTypeHasWidth,
                'filter_by_width' => $request->productTypeFilterByWidth,
                'product_size_width_filter_type_id' => $request->productTypeFilterByWidthFilterTypeId,
                'product_size_width_show_on_main_filter' => $request->productTypeFilterByWidthShowOnMainFilter,
                'product_size_width_filter_full_position_id' => $request->productTypeFilterByWidthFilterFullPositionId,
                'product_size_width_filter_name' => $request->productTypeFilterByWidthName,

                'has_height' => $request->productTypeHasHeight,
                'filter_by_height' => $request->productTypeFilterByHeight,
                'product_size_height_filter_type_id' => $request->productTypeFilterByHeightFilterTypeId,
                'product_size_height_show_on_main_filter' => $request->productTypeFilterByHeightShowOnMainFilter,
                'product_size_height_filter_full_position_id' => $request->productTypeFilterByHeightFilterFullPositionId,
                'product_size_height_filter_name' => $request->productTypeFilterByHeightName,

                'size_points' => $request->productSizePoints,
            ];


            $imagesToDelete = [];
            $postTypeImage = null;
            if( !is_null($request->image) ) {
                $imagesToDelete[] = $productType->image_path;

                $newImagePath = self::PRODUCT_TYPE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);
                $dataToUpdate['image_path'] = $newImagePath . '.webp';

                $postTypeImage['image'] = $request->image;
                $postTypeImage['path'] = $newImagePath;
            }


            $productType->update($dataToUpdate);

            if(!is_null($request->additionalProducts)) {
                $articleIds = explode(",", $request->additionalProducts);
                $productType->products()->sync($articleIds);
            } else {
                $productType->products()->sync([]);
            }

            $productType->sizeFilterOptions()->delete();

            $this->createSizeFilterOptions($productType, $request);

            if ($request->productTypeFields) {
                $productType->fields()->sync($this->prepareProductFieldsToSync($request->productTypeFields));
            } else {
                $productType->fields()->sync([]);
            }

            if ($request->productTypeAttributes) {
                $productType->attributes()->sync($this->prepareProductAttributesToSync($request->productTypeAttributes));
            } else {
                $productType->attributes()->sync([]);
            }

            if( !is_null( $postTypeImage ) ) {
                $this->storeImage($postTypeImage['path'], $postTypeImage['image'], 'webp');
                $this->storeImage($postTypeImage['path'], $postTypeImage['image'], 'jpg');
            }

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteImage($imageToDelete);
            }

            $this->syncFaqs($productType->slug, $request->faqs);

            SeoText::updateSeoText($productType->slug, $request->seoTitle, $request->seoText);

            return ServiceActionResult::make(true, trans('admin.product_type_edit_success'));
        });
    }

    public function deleteProductType(ProductType $productType): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType) {
            if (Product::where('product_type_id', $productType->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_type_in_use'));
            } else {
                 $productType->fields()->sync([]);

                $productType->sizeFilterOptions()->delete();

                $this->syncFaqs($productType->slug, []);

                SeoText::where('page_type', $productType->slug)->delete();

                $this->deleteImage($productType->image_path);

                $productType->delete();

                return ServiceActionResult::make(true, trans('admin.product_type_delete_success'));
            }
        });
    }

    public function searchAdditionalProducts($productType, array $request)
    {
        $query = Product::query();

        if (!is_null($request['excludePostIds'])) {
//            $excludePostIds = explode(",", $request['excludePostIds']);
            $excludePostIds = array_map('intval', explode(",", $request['excludePostIds']));
            $query->whereNotIn('id', $excludePostIds);
        }

        if ( !is_null($request['search']) ) {
            $searchTerm = '%' . $request['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru")) LIKE ? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) LIKE ?', [$searchTerm, $searchTerm])
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.uk")) LIKE ? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.uk"))) LIKE ?', [$searchTerm, $searchTerm]);
            });
        }

        return [
            'documents' => $query->select(['id', 'name'])->limit(6)->get()
        ];
    }

    private function prepareProductFieldsToSync(array $productFields): array
    {
        $result = [];
        foreach ($productFields as $index => $productField) {
            $id = $productField['id'];
            unset($productField['id']);
            $result[$id] = $productField;
        }

        return $result;
    }

    private function prepareProductAttributesToSync(array $productAttributes): array
    {
        $result = [];
        foreach ($productAttributes as $index => $productAttribute) {
            $id = $productAttribute['id'];
            unset($productAttribute['id']);
            $result[$id] = $productAttribute;
        }

        return $result;
    }

    private function createSizeFilterOptions(ProductType $productType, EditProductTypeDTO $request): void
    {
        if ($request->productTypeFilterByLength &&
            $request->productTypeFilterByLengthFilterTypeId ==
            NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

            $optionsPrepared = [];
            foreach ($request->productTypeFilterByLengthOptions as $key => $option) {
                $optionsPrepared[$key] = $option;
                $optionsPrepared[$key]['type'] = ProductSizeTypesDataClass::LENGTH;
            }

            $productType->sizeFilterOptions()->createMany($optionsPrepared);
        }

        if ($request->productTypeFilterByWidth &&
            $request->productTypeFilterByWidthFilterTypeId ==
            NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

            $optionsPrepared = [];
            foreach ($request->productTypeFilterByWidthOptions as $key => $option) {
                $optionsPrepared[$key] = $option;
                $optionsPrepared[$key]['type'] = ProductSizeTypesDataClass::WIDTH;
            }

            $productType->sizeFilterOptions()->createMany($optionsPrepared);
        }

        if ($request->productTypeFilterByHeight &&
            $request->productTypeFilterByHeightFilterTypeId ==
            NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

            $optionsPrepared = [];
            foreach ($request->productTypeFilterByHeightOptions as $key => $option) {
                $optionsPrepared[$key] = $option;
                $optionsPrepared[$key]['type'] = ProductSizeTypesDataClass::WIDTH;
            }


            $productType->sizeFilterOptions()->createMany($optionsPrepared);
        }
    }

}
