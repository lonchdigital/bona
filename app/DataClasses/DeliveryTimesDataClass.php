<?php

namespace App\DataClasses;

class DeliveryTimesDataClass implements BaseDataClass
{
    const MORNING = 1;
    const AFTERNOON = 2;
    const EVENING = 3;

    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::MORNING,
                'name' => '08:00 - 12:00',
            ],
            [
                'id' => self::AFTERNOON,
                'name' => '13:00 - 17:00'
            ],
            [
                'id' => self::EVENING,
                'name' => '18:00 - 20:00'
            ],
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
