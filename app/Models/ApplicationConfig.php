<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ApplicationConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config_data' => 'json',
    ];

}
