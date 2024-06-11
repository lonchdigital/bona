<?php

namespace App\DataClasses;

class PaymentTypesDataClass implements BaseDataClass
{
    const CASH_PAYMENT = 1;
    const CARD_PAYMENT = 2;
    const CARD_PAYMENT_PAYPART = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::CASH_PAYMENT,
                'name' => trans('base.checkout_payment_cash'),
            ],
            [
                'id' => self::CARD_PAYMENT,
                'name' => trans('base.checkout_payment_card'),
            ],
            [
                'id' => self::CARD_PAYMENT_PAYPART,
                'name' => trans('base.checkout_payment_paypart'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
