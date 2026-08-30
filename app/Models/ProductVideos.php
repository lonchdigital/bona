<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductVideos extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['tab'];
}
