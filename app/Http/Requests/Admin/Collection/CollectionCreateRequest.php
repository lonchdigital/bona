<?php

namespace App\Http\Requests\Admin\Collection;

use App\Http\Requests\BaseRequest;
use App\Services\Collection\DTO\EditCollectionDTO;

class CollectionCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $rules =  [
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'string',
            ],
            'slide' => [
                'array',
                'required',
                'min:1',
            ],
            'slide.*.id' => [
                'nullable',
                'exists:collection_slides,id',
            ],
            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['slide.*.image_1'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['slide.*.image_2'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_1'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_2'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_3'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_4'] = [
            'required',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => mb_strtolower(trans('admin.collection_name')),
            'slug' => mb_strtolower(trans('admin.slug')),
            'slide' => mb_strtolower(trans('admin.slides')),
            'collection_preview_image_1' => mb_strtolower(trans('admin.collection_preview_1')),
            'collection_preview_image_2' => mb_strtolower(trans('admin.collection_preview_2')),
            'collection_preview_image_3' => mb_strtolower(trans('admin.collection_preview_3')),
            'collection_preview_image_4' => mb_strtolower(trans('admin.collection_preview_4')),
            'slide.*.image_1' => mb_strtolower(trans('admin.slide_image_1')),
            'slide.*.image_2' => mb_strtolower(trans('admin.slide_image_2')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditCollectionDTO
    {
        return new EditCollectionDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('brand_id'),
            $this->validated('slide'),
            $this->validated('collection_preview_image_1'),
            $this->validated('collection_preview_image_2'),
            $this->validated('collection_preview_image_3'),
            $this->validated('collection_preview_image_4'),
        );
    }
}
