<?php

namespace App\DataClasses;

class PaymentTypesDataClass implements BaseDataClass
{
    const CASH_PAYMENT = 1;

    const CARD_PAYMENT = 2;

    const CARD_PAYMENT_PAYPART = 3;

    const CARD_PAYMENT_PAYPART_MONO_BANK = 4;

    const INVOICE_PAYMENT = 5;

    const MANAGER_CONFIRMATION_PAYMENT = 6;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::CASH_PAYMENT,
                'name' => trans('base.checkout_payment_cash'),
                'internal_name' => 'cash',
            ],
            [
                'id' => self::CARD_PAYMENT,
                'name' => trans('base.checkout_payment_card'),
                'internal_name' => 'card',
            ],
            [
                'id' => self::CARD_PAYMENT_PAYPART,
                'name' => trans('base.checkout_payment_paypart'),
                'internal_name' => 'PP',
            ], [
                'id' => self::CARD_PAYMENT_PAYPART_MONO_BANK,
                'name' => trans('base.checkout_payment_paypart_mono_bank'),
                'internal_name' => 'PP mono',
            ], [
                'id' => self::INVOICE_PAYMENT,
                'name' => trans('base.checkout_payment_invoice'),
                'internal_name' => 'invoice',
            ], [
                'id' => self::MANAGER_CONFIRMATION_PAYMENT,
                'name' => trans('base.checkout_payment_manager_confirmation'),
                'internal_name' => 'manager_confirmation',
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
