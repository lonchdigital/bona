<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config_data' => 'json',
    ];
}
