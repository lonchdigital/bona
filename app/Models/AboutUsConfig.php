<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class AboutUsConfig extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description', 'button_text', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];


    public function imageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->image);
        });
    }
}
