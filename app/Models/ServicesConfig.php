<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServicesConfig extends Model
{
    use HasTranslations;

    public $translatable = ['meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

}
