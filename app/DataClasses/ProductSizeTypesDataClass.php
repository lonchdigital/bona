<?php

namespace App\DataClasses;

class ProductSizeTypesDataClass implements BaseDataClass
{
    const LENGTH = 'LENGTH';
    const WIDTH = 'WIDTH';
    const HEIGHT = 'HEIGHT';

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::LENGTH,
            ],
            [
                'id' => self::WIDTH,
            ],
            [
                'id' => self::HEIGHT,
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
