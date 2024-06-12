<?php

namespace App\DataClasses;

class PartialPaymentStatusDataClass implements BaseDataClass
{
    const CREATED = 'CREATED';
    const CANCELED = 'CANCELED';
    const SUCCESS = 'SUCCESS';
    const FAIL = 'FAIL';
    const CLIENT_WAIT = 'CLIENT_WAIT';
    const OTP_WAITING = 'OTP_WAITING';
    const PP_CREATION = 'PP_CREATION';
    const LOCKED = 'LOCKED';


    public static function get(?int $item = null): mixed
    {
        $collection = collect([
            [
                'id' => self::CREATED,
            ],
            [
                'id' => self::CANCELED,
            ],
            [
                'id' => self::SUCCESS,
            ],
            [
                'id' => self::FAIL,
            ],
            [
                'id' => self::CLIENT_WAIT,
            ],
            [
                'id' => self::OTP_WAITING,
            ],
            [
                'id' => self::PP_CREATION,
            ],
            [
                'id' => self::LOCKED,
            ]
        ]);

        if ($item) {
            return $collection->where('id', $item)->first();
        }

        return $collection;
    }
}
