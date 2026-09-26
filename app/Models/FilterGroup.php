<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class FilterGroup extends Model implements Sitemapable
{
    use HasTranslations;

    public const CONTENT_PAGE_TYPE_PREFIX = 'filter-group:';

    public $translatable = [
        'name',
        'title_tag',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editorialPageType(): string
    {
        return self::CONTENT_PAGE_TYPE_PREFIX.$this->id;
    }

    public function toSitemapTag(): Url|string|array
    {
        if (! $this->productType) {
            return [];
        }

        $path = route('store.catalog.filter-group.page', [
            'productTypeSlug' => $this->productType->slug,
            'filterGroupSlug' => $this->slug,
        ], false);

        return [$path, '/ru'.$path];
    }
}
