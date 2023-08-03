<?php

namespace App\DataClasses;

class NumericFieldFilerTypesDataClass implements BaseDataClass
{
    const NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE = 1;
    const NUMERIC_FILTER_AS_OPTIONS_TYPE = 2;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE,
                'name' => trans('admin.numeric_filter_as_from_to_inputs_type'),
            ],
            [
                'id' => self::NUMERIC_FILTER_AS_OPTIONS_TYPE,
                'name' => trans('admin.numeric_filter_as_options_type'),
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
