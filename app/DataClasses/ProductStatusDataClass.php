<?php

namespace App\DataClasses;

class ProductStatusDataClass implements BaseDataClass
{
    const PRODUCT_STATUS_NONE = 1;
    const PRODUCT_STATUS_STOCK = 2;
    const PRODUCT_STATUS_ORDER = 3;
    const PRODUCT_STATUS_OUT_OF_STOCK = 4;
    const PRODUCT_STATUS_OUT_ASK_MANAGER = 5;
    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::PRODUCT_STATUS_NONE,
                'name' => trans('admin.not_chosen'),
                'trans_key' => 'admin.not_chosen',
            ],
            [
                'id' => self::PRODUCT_STATUS_STOCK,
                'name' => trans('shop.product_status_stock'),
                'trans_key' => 'shop.product_status_stock',
            ],
            [
                'id' => self::PRODUCT_STATUS_ORDER,
                'name' => trans('shop.product_status_order'),
                'trans_key' => 'shop.product_status_order',
            ],
            [
                'id' => self::PRODUCT_STATUS_OUT_OF_STOCK,
                'name' => trans('shop.product_status_out_of_stock'),
                'trans_key' => 'shop.product_status_out_of_stock',
            ],
            [
                'id' => self::PRODUCT_STATUS_OUT_ASK_MANAGER,
                'name' => trans('shop.product_status_ask_manager'),
                'trans_key' => 'shop.product_status_ask_manager',
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }

    public static function getForWeb(): mixed
    {
        return collect([
            [
                'id' => self::PRODUCT_STATUS_STOCK,
                'name' => trans('shop.product_status_stock'),
                'trans_key' => 'shop.product_status_stock',
            ],
            [
                'id' => self::PRODUCT_STATUS_ORDER,
                'name' => trans('shop.product_status_order'),
                'trans_key' => 'shop.product_status_order',
            ],
            [
                'id' => self::PRODUCT_STATUS_OUT_OF_STOCK,
                'name' => trans('shop.product_status_out_of_stock'),
                'trans_key' => 'shop.product_status_out_of_stock',
            ],
            [
                'id' => self::PRODUCT_STATUS_OUT_ASK_MANAGER,
                'name' => trans('shop.product_status_ask_manager'),
                'trans_key' => 'shop.product_status_ask_manager',
            ]
        ]);
    }
}
