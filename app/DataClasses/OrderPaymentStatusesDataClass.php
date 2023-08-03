<?php

namespace App\DataClasses;

class OrderPaymentStatusesDataClass implements BaseDataClass
{
    const STATUS_PAID = 1;
    const STATUS_UNPAID = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_PAID,
                'name' => trans('admin.order_payment_status_paid'),
            ],
            [
                'id' => self::STATUS_UNPAID,
                'name' => trans('admin.order_payment_status_unpaid'),
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
