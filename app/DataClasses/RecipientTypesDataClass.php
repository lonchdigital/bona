<?php

namespace App\DataClasses;

class RecipientTypesDataClass implements BaseDataClass
{
    const RECIPIENT_USER = 1;
    const RECIPIENT_CUSTOM = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::RECIPIENT_USER,
                'name' => trans('base.checkout_recipient_me'),
            ],
            [
                'id' => self::RECIPIENT_CUSTOM,
                'name' => trans('base.checkout_recipient_another_person'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
