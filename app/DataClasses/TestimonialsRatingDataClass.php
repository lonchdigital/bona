<?php

namespace App\DataClasses;

class TestimonialsRatingDataClass implements BaseDataClass
{
    const RATING_ONE = 1;
    const RATING_TWO = 2;
    const RATING_THREE = 3;
    const RATING_FOUR = 4;
    const RATING_FIVE = 5;


    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::RATING_ONE,
                'name' => '1 star',
            ],
            [
                'id' => self::RATING_TWO,
                'name' => '2 stars',
            ],
            [
                'id' => self::RATING_THREE,
                'name' => '3 stars',
            ],
            [
                'id' => self::RATING_FOUR,
                'name' => '4 stars',
            ],
            [
                'id' => self::RATING_FIVE,
                'name' => '5 stars',
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }

    public static function getArray(): array
    {
        return [
            self::RATING_ONE => '1 star',
            self::RATING_TWO => '2 stars',
            self::RATING_THREE => '3 stars',
            self::RATING_FOUR => '4 stars',
            self::RATING_FIVE => '5 stars'
        ];
    }
}
