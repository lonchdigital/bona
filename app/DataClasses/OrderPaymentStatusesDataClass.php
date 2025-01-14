<?php

namespace App\DataClasses;

class OrderPaymentStatusesDataClass implements BaseDataClass
{
    const STATUS_PAID = 1;
    const STATUS_UNPAID = 2;
    const STATUS_PAID_AS_RECEIVED = 3;
    const STATUS_IN_PROGRESS = 4;
    const STATUS_DECLINED = 5;
    const STATUS_PAYPART = 6;
    const REJECTED_BY_CLIENT = 7;
    const CLIENT_PUSH_TIMEOUT = 8;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_PAID,
                'name' => trans('admin.order_payment_status_paid'),
                'color' => '#78df8e'
            ],
            [
                'id' => self::STATUS_UNPAID,
                'name' => trans('admin.order_payment_status_unpaid'),
                'color' => '#76ceff'
            ],
            [
                'id' => self::STATUS_PAID_AS_RECEIVED,
                'name' => trans('admin.order_payment_status_paid_as_received'),
                'color' => '#f9e162',
            ],
            [
                'id' => self::STATUS_IN_PROGRESS,
                'name' => trans('admin.order_status_in_progress'),
                'color' => '#f9e162',
            ],
            [
                'id' => self::STATUS_DECLINED,
                'name' => trans('admin.order_status_declined'),
                'color' => '#ff8080',
            ],
            [
                'id' => self::STATUS_PAYPART,
                'name' => trans('base.checkout_payment_paypart'),
                'color' => '#f9e162',
            ],
            [
                'id' => self::REJECTED_BY_CLIENT,
                'name' => trans('base.rejected_by_client'),
                'color' => '#76ceff',
            ],
            [
                'id' => self::CLIENT_PUSH_TIMEOUT,
                'name' => trans('base.client_push_timeout'),
                'color' => '#76ceff',
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
