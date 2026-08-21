<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AboutUsFact extends Model
{
    use HasTranslations;

    public $translatable = ['label'];

    protected $guarded = [];
}
