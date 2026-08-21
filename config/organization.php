<?php

/*
|--------------------------------------------------------------------------
| Business facts behind the structured data
|--------------------------------------------------------------------------
|
| Addresses, coordinates, opening hours and profiles, kept in one place so the
| Organization / LocalBusiness markup is built from facts rather than written
| out by hand in a template. The contact page still renders from the database;
| this is the part that markup needs and the database has no field for.
|
*/

return [

    'name' => 'Bona',
    'founding_date' => '2013',
    'price_range' => '₴₴',
    'currencies_accepted' => 'UAH',

    'area_served' => [
        'Одеса',
        'Одеська область',
        'Україна',
    ],

    /*
     * Fed into sameAs, which is how a search engine ties the site to the
     * profiles that describe the same business.
     */
    'same_as' => array_values(array_filter([
        'https://www.instagram.com/bona_doors/',
        'https://www.facebook.com/Dveriukraine',
        'https://www.tiktok.com/@bonadoors',
        'https://t.me/salon_dverey_Bona',
        'https://maps.app.goo.gl/hYu3N41k96sStiFD8',
    ])),

    /*
     * Mon-Sat, 9:00 to 18:00. Sunday is left out rather than marked closed,
     * which is how schema.org expects a day off to be expressed.
     */
    'opening_hours' => [
        'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        'opens' => '09:00',
        'closes' => '18:00',
    ],

    'showrooms' => [
        [
            'name' => 'Bona — ТЦ «Гіпермаркет Дверей»',
            'street' => 'вулиця Краснова, 12а',
            'locality' => 'Одеса',
            'region' => 'Одеська область',
            'postal_code' => '65000',
            'country' => 'UA',
            'telephone' => '+380679534774',
            'latitude' => 46.4517423,
            'longitude' => 30.7370547,
        ],
        [
            'name' => 'Bona — ТЦ «МегаДім»',
            // Renamed from Толбухіна; the site, Google Business Profile and
            // the maps have to agree on the current name.
            'street' => 'вулиця Георгія Липського, 135',
            'locality' => 'Одеса',
            'region' => 'Одеська область',
            'postal_code' => '65000',
            'country' => 'UA',
            'telephone' => '+380679534442',
            'latitude' => 46.4278327,
            'longitude' => 30.7269163,
        ],
    ],

    'map_url' => 'https://maps.app.goo.gl/hYu3N41k96sStiFD8',
];
