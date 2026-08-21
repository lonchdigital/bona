<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class Work extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = [
        'name',
        'intro',
        'description',
        'client_quote',
        'service_title',
        'service_description',
        'price_note',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'price_from' => 'decimal:2',
    ];

    protected $guarded = [];


    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
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

    public function images()
    {
        return $this->hasMany(WorkImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Absolute URL of the cover for og:image and structured data.
     */
    public function ogImageUrl(): Attribute
    {
        return Attribute::make(fn () => \App\Helpers\PreviewImage::url($this->image_path));
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function toSitemapTag(): Url | string | array
    {
        return [
            route('store.work.page', ['workSlug' => $this->slug]),
            '/ru' . route('store.work.page', ['workSlug' => $this->slug], false),
        ];
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }
}
