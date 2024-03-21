<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class BlogPageConfig extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['title', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function toSitemapTag(): Url | string | array
    {
        return route('blog.article.page', ['blogPageSlug' => $this->slug]);
    }
}
