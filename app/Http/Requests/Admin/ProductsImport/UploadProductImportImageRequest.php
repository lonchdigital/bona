<?php

namespace App\Http\Requests\Admin\ProductsImport;

use App\DataClasses\ImportedProductImageTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\UploadImportedProductImageDTO;

class UploadProductImportImageRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
            ],
            'type_id' => [
                'required',
                'in:' . ImportedProductImageTypesDataClass::get()->pluck('id')->implode(','),
            ]
        ];
    }

    public function toDTO(): UploadImportedProductImageDTO
    {
        return new UploadImportedProductImageDTO(
            $this->file('file'),
            $this->input('type_id'),
        );
    }
}
