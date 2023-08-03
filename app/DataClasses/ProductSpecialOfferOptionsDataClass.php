<?php

namespace App\DataClasses;

class ProductSpecialOfferOptionsDataClass implements BaseDataClass
{
    const EXCLUSIVE = 1;

    const NEW = 2;

    const SALE = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::EXCLUSIVE,
                'name' => 'EXCLUSIVE',
                'internal_name' => trans('admin.exclusive'),
            ],
            [
                'id' => self::NEW,
                'name' => 'NEW',
                'internal_name' => trans('admin.new'),
            ],
            [
                'id' => self::SALE,
                'name' => 'SALE',
                'internal_name' => trans('admin.sale'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
