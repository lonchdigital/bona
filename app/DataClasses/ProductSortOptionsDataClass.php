<?php

namespace App\DataClasses;

class ProductSortOptionsDataClass implements BaseDataClass
{
    const SORT_BY_POPULARITY = 'popularity';
    const SORT_BY_NEW = 'new';
    const SORT_BY_PRICE_FROM_LOW = 'price_low';
    const SORT_BY_PRICE_FROM_HIGH = 'price_high';

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::SORT_BY_POPULARITY,
                'name' => trans('base.sort_by_popularity'),
                'is_active_by_default' => true,
            ],
            [
                'id' => self::SORT_BY_NEW,
                'name' => trans('base.sort_by_new'),
                'is_active_by_default' => false,
            ],
            [
                'id' => self::SORT_BY_PRICE_FROM_LOW,
                'name' => trans('base.sort_by_price_from_low'),
                'is_active_by_default' => false,
            ],
            [
                'id' => self::SORT_BY_PRICE_FROM_HIGH,
                'name' => trans('base.sort_by_price_from_high'),
                'is_active_by_default' => false,
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
