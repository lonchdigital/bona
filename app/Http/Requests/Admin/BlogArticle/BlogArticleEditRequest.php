<?php

namespace App\Http\Requests\Admin\BlogArticle;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Rules\RequiredImageDeletedRule;

class BlogArticleEditRequest extends BlogArticleCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['hero_image'] = [
            'nullable',
            'mimes:jpeg,png,jpg',
        ];

        $rules['hero_image_deleted'] = [
            'required',
            new RequiredImageDeletedRule(mb_strtolower(trans('admin.blog_article_hero_image'))),
        ];

        $rules['block.*.id'] = [
            'nullable',
        ];

        if ($this->input('block')) {
            foreach ($this->input('block') as $index => $customBlock) {
                if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                    $rules['block.' . $index . '.images.*.image'] = [
                        'nullable',
                        'mimes:jpeg,png,jpg',
                    ];
                    $rules['block.' . $index . '.images.*.image_deleted'] = [
                        'required',
                        new RequiredImageDeletedRule(mb_strtolower(trans('admin.image'))),
                    ];
                } else if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    $rules['block.' . $index . '.quote_author_image'] = [
                        'nullable',
                        'mimes:jpeg,png,jpg',
                    ];
                    $rules['block.' . $index . '.quote_author_image_deleted'] = [
                        'nullable',
                    ];
                } else if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    $rules['block.' . $index . '.sponsor_image'] = [
                        'nullable',
                        'mimes:jpeg,png,jpg',
                    ];
                    $rules['block.' . $index . '.sponsor_image_deleted'] = [
                        'required',
                        new RequiredImageDeletedRule(mb_strtolower(trans('admin.image'))),
                    ];
                } else if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    $rules['block.' . $index . '.images.*.image'] = [
                        'nullable',
                        'mimes:jpeg,png,jpg',
                       // 'dimensions:ratio=2/1,ratio=1/2',
                    ];
                    $rules['block.' . $index . '.images.*.image_deleted'] = [
                        'required',
                        new RequiredImageDeletedRule(mb_strtolower(trans('admin.image'))),
                    ];
                }
            }
        }

        return $rules;
    }
}
