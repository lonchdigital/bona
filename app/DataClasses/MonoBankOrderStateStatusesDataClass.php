<?php

namespace App\DataClasses;

class MonoBankOrderStateStatusesDataClass implements BaseDataClass
{
    const STATUS_REJECTED = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_RETURNED = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_REJECTED,
                'name' => trans('admin.order_rejected'),
                'color' => '#78df8e'
            ],
            [
                'id' => self::STATUS_CONFIRMED,
                'name' => trans('admin.order_confirmed_getting_product'),
                'color' => '#76ceff'
            ],
            [
                'id' => self::STATUS_RETURNED,
                'name' => trans('admin.order_returned_money'),
                'color' => '#f9e162',
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
