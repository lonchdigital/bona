<?php

namespace App\Http\Requests\Admin\HomePage;

use App\Http\Requests\BaseRequest;
use App\Models\HomePageConfig;
use App\Rules\RequiredImageDeletedRule;
use App\Services\HomePage\DTO\HomePageEditDTO;

class HomePageEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'collection_id' => [
                'required',
                'exists:collections,id'
            ],
            'slider_logo' => [
                HomePageConfig::first() ? 'nullable' : 'required',
                'image',
                'dimensions:ratio=1/1'
            ],
            'slider_logo_deleted' => [
                'nullable',
                new RequiredImageDeletedRule(mb_strtolower(trans('admin.slider_logo'))),
            ],
            'slides' => [
                'array',
            ],
            'slides.*.id' => [
                'nullable'
            ],
            'selected_brands_id' => [
                'required',
                'exists:brands,id',
            ],
            'selected_field_id' => [
                'required',
                'exists:product_fields,id',
            ],
            'selected_field_options_id' => [
                'required',
                'exists:product_field_options,id',
            ]
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $rules['slides.' . $index . '.image'] = [
                    (isset($slide['id']) && $slide['id']) ? 'nullable' : 'required',
                    'image',
                ];
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['slides.*.description.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['slider_title.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'collection_id' => mb_strtolower(trans('admin.collection_on_slide')),
            'slider_logo' => mb_strtolower(trans('admin.slider_logo')),
            'slides.*.image' => mb_strtolower(trans('admin.slide_image')),
            'selected_brands_id' => mb_strtolower(trans('admin.brands')),
            'selected_field_id' => mb_strtolower(trans('admin.field')),
            'selected_field_options_id' => mb_strtolower(trans('admin.field_options')),
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['slides.*.description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_description'), $availableLanguage);
            $attributes['slider_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slider_title'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): HomePageEditDTO
    {
        return new HomePageEditDTO(
            $this->input('slider_title'),
            $this->input('collection_id'),
            $this->file('slider_logo'),
            $this->validated('slides'),
            explode(',', $this->input('selected_brands_id')),
            $this->input('selected_field_id'),
            explode(',', $this->input('selected_field_options_id')),
        );
    }
}
