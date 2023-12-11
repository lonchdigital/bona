<?php

namespace App\DataClasses;

class BlogArticleBlockTypesDataClass implements BaseDataClass
{
    const TYPE_TEXT = 1;
    const TYPE_IMAGE = 2;
    const TYPE_QUOTE = 3;
    const TYPE_SPONSOR = 4;
    const TYPE_VIDEO = 5;
    const TYPE_SLIDER = 6;
    const TYPE_QUESTIONS_AND_ANSWERS = 7;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::TYPE_TEXT,
                'name' => trans('admin.blog_article_block_text'),
            ],
            [
                'id' => self::TYPE_IMAGE,
                'name' => trans('admin.blog_article_block_image'),
            ],
            [
                'id' => self::TYPE_VIDEO,
                'name' => trans('admin.blog_article_block_video'),
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
