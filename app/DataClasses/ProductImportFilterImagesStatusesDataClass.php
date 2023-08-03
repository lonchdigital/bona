<?php

namespace App\DataClasses;

class ProductImportFilterImagesStatusesDataClass implements BaseDataClass
{
    const STATUS_EXISTS = 1;
    const STATUS_NOT_EXISTS = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::STATUS_EXISTS,
                'name' => trans('admin.exists'),
            ],
            [
                'id' => self::STATUS_NOT_EXISTS,
                'name' => trans('admin.not_exists'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
