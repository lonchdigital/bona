<?php

namespace App\DataClasses;

class VisitRequestStatusesDataClass implements BaseDataClass
{
    const STATUS_NEW = 1;
    const STATUS_HANDLED = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_NEW,
                'name' => trans('admin.visit_request_status_new'),
                'color' => '#76ceff',
            ],
            [
                'id' => self::STATUS_HANDLED,
                'name' => trans('admin.visit_request_status_handled'),
                'color' => '#78df8e',
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
