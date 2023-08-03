<?php

namespace App\DataClasses;

use Illuminate\Support\Collection;

class ProductFieldTypeOptionsDataClass implements BaseDataClass
{
    const FIELD_TYPE_STRING = 1;
    const FIELD_TYPE_NUMBER = 2;
    const FIELD_TYPE_SIZE = 3;
    const FIELD_TYPE_OPTION = 4;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::FIELD_TYPE_STRING,
                'name' => trans('shop.product_field_type_string'),
            ],
            [
                'id' => self::FIELD_TYPE_NUMBER,
                'name' => trans('shop.product_field_type_number'),
            ],
            [
                'id' => self::FIELD_TYPE_SIZE,
                'name' => trans('shop.product_field_type_size'),
            ],
            [
                'id' => self::FIELD_TYPE_OPTION,
                'name' => trans('shop.product_field_type_option'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
