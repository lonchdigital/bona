<?php

namespace App\Http\Requests\Admin\ProductSubtype;

use App\Http\Requests\BaseRequest;
use App\Services\Admin\ProductSubtype\DTO\EditProductSubtypeDTO;

class EditProductSubtypeRequest extends BaseRequest
{
    protected array $sizeTypes = ['length', 'width', 'height'];

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'unique:product_types,slug' . (!$this->route('productType') ? '' : ',' . $this->route('productType')->id),
                'string',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }


        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field.*.id' => mb_strtolower(trans('admin.product_field')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditProductSubtypeDTO
    {
        return new EditProductSubtypeDTO(
            $this->input('name'),
            $this->input('slug'),
        );
    }
}
