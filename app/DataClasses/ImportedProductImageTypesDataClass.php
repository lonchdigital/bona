<?php

namespace App\DataClasses;

class ImportedProductImageTypesDataClass implements BaseDataClass
{
    const TYPE_MAIN_IMAGE = 1;
    const TYPE_PATTERN_IMAGE = 2;
    const TYPE_GALLERY_IMAGE_1 = 3;
    const TYPE_GALLERY_IMAGE_2 = 4;
    const TYPE_GALLERY_IMAGE_3 = 5;
    const TYPE_GALLERY_IMAGE_4 = 6;
    const TYPE_GALLERY_IMAGE_5 = 7;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::TYPE_MAIN_IMAGE,
                'name' => 'MAIN_IMAGE',
            ],
            [
                'id' => self::TYPE_PATTERN_IMAGE,
                'name' => 'PATTERN_IMAGE',
            ],
            [
                'id' => self::TYPE_GALLERY_IMAGE_1,
                'name' => 'GALLERY_IMAGE_1',
            ],
            [
                'id' => self::TYPE_GALLERY_IMAGE_2,
                'name' => 'GALLERY_IMAGE_2',
            ],
            [
                'id' => self::TYPE_GALLERY_IMAGE_3,
                'name' => 'GALLERY_IMAGE_3',
            ],
            [
                'id' => self::TYPE_GALLERY_IMAGE_4,
                'name' => 'GALLERY_IMAGE_4',
            ],
            [
                'id' => self::TYPE_GALLERY_IMAGE_5,
                'name' => 'GALLERY_IMAGE_5',
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
