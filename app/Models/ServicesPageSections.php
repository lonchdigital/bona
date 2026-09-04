<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class ServicesPageSections extends Model implements Sitemapable
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = [
        'title',
        'description',
        'button_text',
        'intro',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function sectionImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if (! $this->section_image_path) {
                return null;
            }

            if (str_starts_with($this->section_image_path, 'assets/')) {
                return asset('/'.$this->section_image_path);
            }

            return Storage::url($this->section_image_path);
        });
    }

    public function toSitemapTag(): Url|string|array
    {
        if (! $this->slug) {
            return [];
        }

        return [
            route('store.service.page', ['serviceSlug' => $this->slug]),
            '/ru'.route('store.service.page', ['serviceSlug' => $this->slug], false),
        ];
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['section_image_url'] = $this->section_image_url;

        return $array;
    }
}
