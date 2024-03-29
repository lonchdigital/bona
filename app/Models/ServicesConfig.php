<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;
use Spatie\Sitemap\Contracts\Sitemapable;

class ServicesConfig extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function toSitemapTag(): Url | string | array
    {
        $urls = [];
        $urls[] = route('store.services');
        $urls[] = '/ru' . route('store.services', [], false);

        return $urls;
    }

}
