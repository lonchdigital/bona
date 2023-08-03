<?php

namespace App\DataClasses;

class VisitRequestTypeDataClass implements BaseDataClass
{
    const SHOWROOM_VISIT = 1;
    const SHOWROOM_TAXI = 2;
    const DESIGNER_APPOINTMENT = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::SHOWROOM_VISIT,
                'name' => trans('admin.showroom_visit_request'),
            ],
            [
                'id' => self::SHOWROOM_TAXI,
                'name' => trans('admin.showroom_taxi_request'),
            ],
            [
                'id' => self::DESIGNER_APPOINTMENT,
                'name' => trans('admin.designer_appointment_request'),
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
