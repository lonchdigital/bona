<?php

namespace App\DataClasses;

class StaticPageTypesDataClass implements BaseDataClass
{
    const PAGE_DELIVERY_AND_PAYMENT = 1;
    const PAGE_CONTACTS = 2;
    const PAGE_FAQ = 3;
    const PAGE_CONDITIONS = 4;
    const PAGE_POLICY = 5;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::PAGE_DELIVERY_AND_PAYMENT,
                'name' => trans('base.delivery_and_payment'),
                'slug' => 'dostavka-i-oplata',
            ],
            [
                'id' => self::PAGE_CONTACTS,
                'name' => trans('base.contacts'),
                'slug' => 'kontakty',
            ],
            [
                'id' => self::PAGE_FAQ,
                'name' => trans('base.faq'),
                'slug' => 'faq',
            ],
            [
                'id' => self::PAGE_CONDITIONS,
                'name' => trans('base.conditions'),
                'slug' => 'umovy-vykorystannya-saitu',
            ],
            [
                'id' => self::PAGE_POLICY,
                'name' => trans('base.policy'),
                'slug' => 'polityka-konfidencinosti',
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
