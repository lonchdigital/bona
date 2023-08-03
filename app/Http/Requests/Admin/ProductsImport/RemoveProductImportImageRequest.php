<?php

namespace App\Http\Requests\Admin\ProductsImport;

use App\Http\Requests\BaseRequest;
use App\DataClasses\ImportedProductImageTypesDataClass;
use App\Services\Product\DTO\RemoveProductImportImageDTO;

class RemoveProductImportImageRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'type_id' => [
                'required',
                'in:' . ImportedProductImageTypesDataClass::get()->pluck('id')->implode(','),
            ]
        ];
    }

    public function toDTO(): RemoveProductImportImageDTO
    {
        return new RemoveProductImportImageDTO(
            $this->input('type_id')
        );
    }
}
