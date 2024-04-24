<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class HomePageConfig extends Model
{
    use HasTranslations;

    public $translatable = ['slider_title', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];


    public function productField()
    {
        return $this->belongsTo(ProductField::class);
    }

    public function sliderLogoImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->slider_logo_image_path);
        });
    }
}
