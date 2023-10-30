<?php

namespace App\Services\Admin\ProductField;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Models\Product;
use App\Models\ProductCustomField;
use App\Services\Base\ServiceActionResult;
use Carbon\Carbon;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Services\Admin\ProductField\DTO\EditProductFieldDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductFieldService extends BaseService
{
    public const OPTION_IMAGES_FOLDER = 'field-option-images';

    public function getProductFields(): Collection
    {
        return ProductField::get();
    }
    public function getProductFieldsPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductField::with('creator')
            ->paginate(config('domain.items_per_page'));
    }

    public function createProductField(EditProductFieldDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithDBTransaction(function () use($request, $creator) {
            $productField = ProductField::create([
                'creator_id' => $creator->id,
                'field_name' => $request->productFieldName,
                'slug' => $request->slug,
                'field_type_id' => $request->productFieldType,
                'field_size_name' => $request->productFieldSizeName,
                'is_multiselectable' => $request->isMultiselectable,
                'as_image' => $request->asImage,
                'display_on_single' => $request->displayOnSingle,
                'numeric_field_filter_type_id' => $request->numericFieldFilterType,
            ]);

            if ($request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                $productFiledOptions = [];
                foreach ($request->productFieldOptions as $fieldOption) {
                    $optionData = [
                        'name' => $fieldOption['name'],
                        'slug' => $fieldOption['slug'],
                    ];

                    if ($request->asImage) {
                        $optionData['image_path'] = $this->storeOptionImage($fieldOption['image']);
                    }

                    $productFiledOptions[] = $optionData;
                }

                $productField->options()->createMany($productFiledOptions);
            }

            if (($request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
                $request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER) &&
            $request->numericFieldFilterType === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

                $numericFiledFilterOptions = [];
                foreach ($request->numericFiledFilterOptions as $option) {
                    $numericFiledFilterOptions[] = [
                        'name' => $option['name'],
                        'slug' => $option['slug'],
                        'from' => $option['from'],
                        'to' => $option['to'],
                    ];
                }

                $productField->fieldFilterOptions()->createMany($numericFiledFilterOptions);
            }

            return ServiceActionResult::make(true, trans('admin.product_field_create_success'));
        });
    }

    public function updateProductField(ProductField $productField, EditProductFieldDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productField, $request) {
            $productField->update([
                'field_name' => $request->productFieldName,
                'slug' => $request->slug,
                'field_type_id' => $request->productFieldType,
                'field_size_name' => $request->productFieldSizeName,
                'is_multiselectable' => $request->isMultiselectable,
                'as_image' => $request->asImage,
                'display_on_single' => $request->displayOnSingle,
                'numeric_field_filter_type_id' => $request->numericFieldFilterType,
            ]);

            if ($request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                //we have to delete all images at last step, to avoid cases when transaction will be rolled back, but images will be deleted
                $replacedImages = [];

                //options
                $existingOptions = $productField->options;

                $productFieldsIdsToUpdateOrCreate = [];
                $productFieldsToCreate = [];
                foreach ($request->productFieldOptions as $id => $fieldOption) {
                    $productFieldsIdsToUpdateOrCreate[] = $id;
                    $existingOption = $existingOptions->where('id', $id)->first();

                    $optionData = [
                        'name' => $fieldOption['name'],
                        'slug' => $fieldOption['slug'],
                    ];

                    //store image if image was provided
                    if ($request->asImage && isset($fieldOption['image'])) {

                        $optionData['image_path'] = $this->storeOptionImage($fieldOption['image']);
                    }

                    //handle existing options
                    if ($existingOption) {
                        //replace images is new images were provided and old record has image
                        if($request->asImage && isset($fieldOption['image']) && $existingOption['image_path']) {
                            $oldImagePath = Storage::disk(config('app.images_disk_default'))->path($existingOption['image_path']);
                            $replacedImages[] = $oldImagePath;
                        }

                        $existingOptions
                            ->where('id', $id)
                            ->first()
                            ->update($optionData);
                    //handle new options
                    } else {
                        $productFieldsToCreate[] = $optionData;
                    }
                }

                //delete existing options
                $itemsToDelete = $existingOptions->whereNotIn('id', $productFieldsIdsToUpdateOrCreate);

                if (count($itemsToDelete)) {
                    $productField->options()->whereIn('id', $itemsToDelete->pluck('id'))->delete();
                }

                //update existing options
                $productField->options()->saveMany($existingOptions);

                //create new options
                if (count($productFieldsToCreate)) {
                    $productField->options()->createMany($productFieldsToCreate);
                }

                //delete images that were replaces by new images
                foreach ($replacedImages as $replacedImage) {
                    if (Storage::disk(config('app.images_disk_default'))->exists($replacedImage)) {
                        Storage::disk(config('app.images_disk_default'))->delete($replacedImage);
                    }
                }

                //delete images of deleted options
                foreach ($itemsToDelete as $itemToDelete) {
                    if ($itemToDelete['image_path'] && Storage::disk(config('app.images_disk_default'))->exists($itemToDelete['image_path'])) {
                        Storage::disk(config('app.images_disk_default'))->delete($itemToDelete['image_path']);
                    }
                }
            }

            if (($request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
                    $request->productFieldType === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER) &&
                $request->numericFieldFilterType === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                //numeric fields
                $existingNumericFieldOptions = $productField->fieldFilterOptions;
                $numericFieldsOptionIdsToUpdateOrCreate = [];
                $numericFieldsOptionToCreate = [];
                foreach ($request->numericFiledFilterOptions as $id => $numericFiledFilterOption) {
                    $numericFieldsOptionIdsToUpdateOrCreate[] = $id;
                    $existingNumericFieldOption = $existingNumericFieldOptions->where('id', $id)->first();

                    $numericFiledFilterOptionData = [
                        'name' => $numericFiledFilterOption['name'],
                        'slug' => $numericFiledFilterOption['slug'],
                        'from' => $numericFiledFilterOption['from'],
                        'to' => $numericFiledFilterOption['to'],
                    ];

                    //handle existing options
                    if ($existingNumericFieldOption) {
                        $existingNumericFieldOptions
                            ->where('id', $id)
                            ->first()
                            ->update($numericFiledFilterOptionData);
                        //handle new options
                    } else {
                        $numericFieldsOptionToCreate[] = $numericFiledFilterOptionData;
                    }
                }

                //create new options
                if (count($numericFieldsOptionToCreate)) {
                    $productField->fieldFilterOptions()->createMany($numericFieldsOptionToCreate);
                }

                //update existing options
                $productField->fieldFilterOptions()->saveMany($existingNumericFieldOptions);

                //delete existing options
                $numericFieldsOptionToDelete = $existingNumericFieldOptions->whereNotIn('id', $numericFieldsOptionIdsToUpdateOrCreate);

                if (count($numericFieldsOptionToDelete)) {
                    $productField->fieldFilterOptions()->whereIn('id', $numericFieldsOptionToDelete->pluck('id'))->delete();
                }
            }

            return ServiceActionResult::make(true, trans('admin.product_field_edit_success'));
        });
    }

    private function storeOptionImage($uploadedImage): string
    {
        $newImagePath = self::OPTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';

        $image = Image::make($uploadedImage)
            ->resize(150, 150)
            ->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($newImagePath, $image);

        return $newImagePath;
    }

    public function deleteProductField(ProductField $productField): ServiceActionResult
    {
        if (count($productField->types)) {
            return ServiceActionResult::make(false, trans('admin.product_field_in_use'));
        }

        return $this->coverWithDBTransaction(function () use ($productField) {

            $imagesToDelete = [];

            foreach ($productField->options as $option) {
                if ($option->image_url) {
                    $imagesToDelete[] = $option->image_path;
                }
            }

            $productField->fieldFilterOptions()->delete();

            $productField->options()->delete();

            $productField->delete();

            if(count($imagesToDelete)) {
                foreach ($imagesToDelete as $imageToDelete) {
                    if (Storage::disk(config('app.images_disk_default'))->exists($imageToDelete)) {
                        Storage::disk(config('app.images_disk_default'))->delete($imageToDelete);
                    }
                }
            }

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
