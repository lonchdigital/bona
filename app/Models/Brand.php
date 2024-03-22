<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Brand extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'description', 'slider_main_text', 'slider_description_text', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function slides()
    {
        return $this->hasMany(BrandSlide::class);
    }

    public function logoImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->logo_image_path);
        });
    }


    public function toArray()
    {
        $array = parent::toArray();

        $array['logo_image_url'] = $this->logo_image_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        return route('store.brand.page', ['brandSlug' => $this->slug]);
    }

}
