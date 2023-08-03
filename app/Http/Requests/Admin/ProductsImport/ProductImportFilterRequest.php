<?php

namespace App\Http\Requests\Admin\ProductsImport;

use App\DataClasses\ProductImportFilterImagesStatusesDataClass;
use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\ProductImportFilterDTO;

class ProductImportFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        $imagesFiltersString = ProductImportFilterImagesStatusesDataClass::get()->pluck('id')->implode(',');

        return [
            'search' => [
                'nullable',
                'string',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],
            'color_id' => [
                'nullable',
                'integer',
                'exists:colors,id',
            ],
            'collection_id' => [
                'nullable',
                'integer',
                'exists:collections,id',
            ],
            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],
            'main_image' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'patter_image' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'gallery_image_1' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'gallery_image_2' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'gallery_image_3' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'gallery_image_4' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'gallery_image_5' => [
                'nullable',
                'integer',
                'in:' . $imagesFiltersString,
            ],
            'show_new' => [
                'nullable',
                'bool',
            ],
            'show_existing' => [
                'nullable',
                'bool',
            ]
        ];
    }

    public function toDTO(): ProductImportFilterDTO
    {
        return new ProductImportFilterDTO(
            $this->input('search'),
            $this->input('brand_id'),
            $this->input('color_id'),
            $this->input('collection_id'),
            $this->input('country_id'),
            $this->input('main_image'),
            $this->input('patter_image'),
            $this->input('gallery_image_1'),
            $this->input('gallery_image_2'),
            $this->input('gallery_image_3'),
            $this->input('gallery_image_4'),
            $this->input('gallery_image_5'),
            (bool) $this->input('show_new'),
            (bool) $this->input('show_existing'),
        );
    }
}
