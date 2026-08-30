<?php

namespace App\DataClasses;

class ProductReviewStatusesDataClass implements BaseDataClass
{
    const STATUS_PENDING = 1;

    const STATUS_APPROVED = 2;

    const STATUS_REJECTED = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_PENDING,
                'name' => trans('admin.product_review_status_pending'),
                'color' => '#76ceff',
            ],
            [
                'id' => self::STATUS_APPROVED,
                'name' => trans('admin.product_review_status_approved'),
                'color' => '#78df8e',
            ],
            [
                'id' => self::STATUS_REJECTED,
                'name' => trans('admin.product_review_status_rejected'),
                'color' => '#ff9a9a',
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
