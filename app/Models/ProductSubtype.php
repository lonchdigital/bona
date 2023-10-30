<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class ProductSubtype extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = [
        'name',
    ];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }


    public function toSitemapTag(): Url | string | array
    {
        return route('store.catalog.page', ['productTypeSlug' => $this->slug]);
    }
}
