<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bank credentials
    |--------------------------------------------------------------------------
    |
    | These were read with env() from inside the services that use them. That
    | works only for as long as nobody caches the configuration: after a
    | php artisan config:cache — an ordinary deployment step — env() returns
    | null everywhere outside this directory, and the signature check would
    | have started comparing against an empty password rather than failing
    | loudly. Read through config() they survive caching.
    |
    */

    'privatbank' => [

        'store_id' => env('PRIVATBANK_STORE_ID', ''),
        'password' => env('PRIVATBANK_PASSWORD', ''),

        /*
        | How many payments the bank is offered for. One list, read by the form
        | that shows the choice and by the validation that accepts it back —
        | they used to be written out separately, and the validation only asked
        | for an integer, so a crafted request could name a number the shop
        | never offered.
        |
        | The bank itself takes 2 to 25. What belongs here is what the shop's
        | agreement covers.
        */
        'minimum_period' => 2,
        'periods' => [2, 3, 4, 5, 6, 7, 8, 9, 10],

        /*
        | The surcharge is part of the financed order total. These are the
        | final rates charged to the customer (the bank's rate plus the
        | additional 1.3 percentage points supplied by the shop). Keeping the
        | final number here means the browser, order snapshot and bank request
        | all use the same table.
        */
        'installment_surcharges' => [
            2 => 3.5,
            3 => 3.8,
            4 => 4.9,
            5 => 6.6,
            6 => 7.8,
            7 => 9.0,
            8 => 10.1,
            9 => 11.2,
            10 => 12.5,
        ],

    ],

    'monobank' => [

        'api_url' => env('MONOBANK_API_URL', ''),
        'client_secret' => env('MONOBANK_CLIENT_SECRET', ''),
        'store_id' => env('MONOBANK_CLIENT_STORE_ID', ''),
        'point_id' => env('MONOBANK_POINT_ID', ''),

        // The bank itself takes 3 to 25.
        'minimum_period' => 3,
        'periods' => [3, 4, 5, 6, 7, 8, 9, 10],
        'installment_surcharges' => [
            3 => 2.9,
            4 => 4.1,
            5 => 5.9,
            6 => 7.2,
            7 => 8.3,
            8 => 9.5,
            9 => 10.8,
            10 => 12.0,
        ],

    ],

];
