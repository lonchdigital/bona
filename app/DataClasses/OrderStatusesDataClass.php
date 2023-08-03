<?php

namespace App\DataClasses;

class OrderStatusesDataClass implements BaseDataClass
{
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_SENT = 3;
    const STATUS_COMPLETE = 4;
    const STATUS_DECLINED = 5;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_NEW,
                'name' => trans('admin.order_status_new'),
                'color' => '#76ceff',
            ],
            [
                'id' => self::STATUS_IN_PROGRESS,
                'name' => trans('admin.order_status_in_progress'),
                'color' => '#f9e162',
            ],
            [
                'id' => self::STATUS_SENT,
                'name' => trans('admin.order_status_sent'),
                'color' => '#ffb57f',
            ],
            [
                'id' => self::STATUS_COMPLETE,
                'name' => trans('admin.order_status_complete'),
                'color' => '#78df8e',
            ],
            [
                'id' => self::STATUS_DECLINED,
                'name' => trans('admin.order_status_declined'),
                'color' => '#ff8080',
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
