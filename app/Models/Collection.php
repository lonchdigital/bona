<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class Collection extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function slides()
    {
        return $this->hasMany(CollectionSlide::class, 'collection_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'collection_id');
    }

    public function previewImage1Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->preview_image_1) {
                return Storage::url($this->preview_image_1);
            }
            return null;
        });
    }

    public function previewImage2Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->preview_image_2) {
                return Storage::url($this->preview_image_2);
            }
            return null;
        });
    }

    public function previewImage3Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->preview_image_3) {
                return Storage::url($this->preview_image_3);
            }
            return null;
        });
    }

    public function previewImage4Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->preview_image_3) {
                return Storage::url($this->preview_image_4);
            }
            return null;
        });
    }

    public function toSitemapTag(): Url | string | array
    {
        return route('store.collection.page', ['collectionSlug' => $this->slug]);
    }
}
