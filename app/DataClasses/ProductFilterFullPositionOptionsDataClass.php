<?php

namespace App\DataClasses;

class ProductFilterFullPositionOptionsDataClass implements BaseDataClass
{
    const FILTER_POSITION_LEFT = 1;
    const FILTER_POSITION_MIDDLE = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::FILTER_POSITION_LEFT,
                'name' => trans('admin.filter_position_left'),
            ],
            [
                'id' => self::FILTER_POSITION_MIDDLE,
                'name' => trans('admin.filter_position_middle'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
