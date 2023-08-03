<?php

namespace App\Http\Requests\Admin\BlogSlides;

use App\Http\Requests\BaseRequest;
use App\Rules\RequiredImageDeletedRule;
use App\Services\BlogSlides\DTO\BlogSlidesEditDTO;

class BlogSlidesEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'slides.*.id' => [
                'nullable',
                'exists:blog_slides,id',
            ],
            'slides.*.collection_id' => [
                'required',
                'exists:collections,id'
            ],
            'slides.*.description' => [
                'array',
            ],
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                if (isset($slide['id'])) {
                    $rules['slides.' . $index . '.image_1'] = [
                        'nullable',
                        'image',
                        'dimensions:ratio=1/1'
                    ];
                    $rules['slides.' . $index . '.image_1_deleted'] = [
                        'required',
                        new RequiredImageDeletedRule(mb_strtolower(trans('admin.slide_image_1'))),
                    ];
                    $rules['slides.' . $index . '.image_2'] = [
                        'nullable',
                        'image',
                        'dimensions:ratio=1/1'
                    ];
                    $rules['slides.' . $index . '.image_2_deleted'] = [
                        'required',
                        new RequiredImageDeletedRule(mb_strtolower(trans('admin.slide_image_1'))),
                    ];
                } else {

                    $rules['slides.' . $index . '.image_1'] = [
                        'required',
                        'image',
                        'dimensions:ratio=1/1'
                    ];

                    $rules['slides.' . $index . '.image_2'] = [
                        'required',
                        'image',
                        'dimensions:ratio=1/1'
                    ];
                }
            }
        }


        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['slides.*.description.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slides.*.collection_id' => mb_strtolower(trans('admin.collection_on_slide')),
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $attributes['slides.' . $index . '.image_1'] = mb_strtolower(trans('admin.slide_image_1'));
                $attributes['slides.' . $index . '.image_2'] = mb_strtolower(trans('admin.slide_image_2'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['slides.*.description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_description'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): BlogSlidesEditDTO
    {
        return new BlogSlidesEditDTO(
            $this->validated('slides')
        );
    }
}
