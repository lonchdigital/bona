<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContactConfig extends Model
{
    use HasTranslations;

    public $translatable = [
        'city_one',
        'address_one',
        'phone_one',
        'email_one',
        'working_hours_one',
        'city_two',
        'address_two',
        'phone_two',
        'email_two',
        'working_hours_two',
        'city_three',
        'address_three',
        'phone_three',
        'email_three',
        'working_hours_three',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $guarded = [];
}
