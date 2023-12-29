<?php

namespace App\DataClasses;

class DeliveryTypesDataClass implements BaseDataClass
{
    const ADDRESS_DELIVERY = 1;
    const NP_DELIVERY = 2;
    const MIST_EXPRESS_DELIVERY = 3;
    const PICK_UP_DELIVERY = 4;
    const SAT_DELIVERY = 5;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::ADDRESS_DELIVERY,
                'name' => trans('base.checkout_address_delivery'),
            ],
            [
                'id' => self::NP_DELIVERY,
                'name' => trans('base.checkout_np_delivery'),
            ],
            [
                'id' => self::MIST_EXPRESS_DELIVERY,
                'name' => trans('base.checkout_ukr_p_delivery'),
            ],
            [
                'id' => self::PICK_UP_DELIVERY,
                'name' => trans('base.checkout_pickup_from_store'),
            ],
            [
                'id' => self::SAT_DELIVERY,
                'name' => trans('base.checkout_sat_delivery'),
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
