<?php

namespace App\Models;

use App\Services\Brand\BrandCatalogUrlService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class Brand extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'description', 'slider_main_text', 'slider_description_text', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    public function toSitemapTag(): Url|string|array
    {
        $productType = app(BrandCatalogUrlService::class)->preferredProductType($this);

        if (! $productType) {
            return [];
        }

        $routeParameters = [
            'productTypeSlug' => $productType->slug,
            'brandSlug' => $this->slug,
        ];

        return [
            route('store.catalog.manufacturer.page', $routeParameters),
            '/ru'.route('store.catalog.manufacturer.page', $routeParameters, false),
        ];
    }
}
