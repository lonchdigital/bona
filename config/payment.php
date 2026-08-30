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
        'periods' => [2, 3, 4, 5, 6],

    ],

    'monobank' => [

        'api_url' => env('MONOBANK_API_URL', ''),
        'client_secret' => env('MONOBANK_CLIENT_SECRET', ''),
        'store_id' => env('MONOBANK_CLIENT_STORE_ID', ''),
        'point_id' => env('MONOBANK_POINT_ID', ''),

        // The bank itself takes 3 to 25.
        'periods' => [3, 4, 5],

    ],

];
