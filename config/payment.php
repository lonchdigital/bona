<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Instalment periods
    |--------------------------------------------------------------------------
    |
    | How many payments each bank is offered for. One list, read by the form
    | that shows the choice and by the validation that accepts it back — they
    | used to be written out separately, and the validation only asked for an
    | integer, so a crafted request could name a number the shop never offered.
    |
    | The banks themselves accept more than this: PrivatBank 2 to 25, Monobank
    | 3 to 25. What belongs here is what the shop's agreement covers.
    |
    */

    'privatbank' => [
        'periods' => [2, 3, 4, 5, 6],
    ],

    'monobank' => [
        'periods' => [3, 4, 5],
    ],

];
