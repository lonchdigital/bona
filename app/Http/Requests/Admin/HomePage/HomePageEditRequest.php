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
            /*
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
            ],*/
            'slides.*.id' => [
                'nullable'
            ],
            'selected_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            'selected_best_sales_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            /*'selected_field_id' => [
                'required',
                'exists:product_fields,id',
            ],
            'selected_field_options_id' => [
                'required',
                'exists:product_field_options,id',
            ]*/
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {

                $rules['slides.' . $index . '.image'] = [
                    (isset($slide['id']) && $slide['id']) ? 'nullable' : 'required',
                    'image',
                ];

                $rules['slides.' . $index . '.button_url'] = [
                    'required',
                    'string'
                ];

            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['slides.*.title.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['slides.*.button_text.' . $availableLanguage] = [
                'required',
                'string'
            ];

            $rules['faqs.*.question.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['faqs.*.answer.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slider_logo' => mb_strtolower(trans('admin.slider_logo')),
            'slides.*.image' => mb_strtolower(trans('admin.slide_image')),
            'selected_field_id' => mb_strtolower(trans('admin.field')),
//            'selected_field_options_id' => mb_strtolower(trans('admin.field_options')),
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));

                $attributes['slides.' . $index . '.button_url'] = mb_strtolower(trans('admin.slide_text_link'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['slides.*.title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_title'), $availableLanguage);
            $attributes['slides.*.button_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_text_button'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): HomePageEditDTO
    {
        return new HomePageEditDTO(
//            $this->input('slider_title'),
//            $this->file('slider_logo'),
            $this->validated('slides'),
            explode(',', $this->input('selected_products_id')),
            explode(',', $this->input('selected_best_sales_products_id')),
            $this->validated('faqs'),

//            $this->input('selected_field_id'),
//            explode(',', $this->input('selected_field_options_id')),
        );
    }
}
