<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->image_path) {
                return Storage::url($this->image_path);
            }
            return null;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        return route('store.catalog-category.page', ['categorySlug' => $this->slug, 'productTypeSlug' => $this->productType->slug]);
    }
}
