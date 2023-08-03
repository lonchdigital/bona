<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class BlogArticle extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'sub_title', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function blocks()
    {
        return $this->hasMany(BlogArticleBlock::class);
    }

    public function heroImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->hero_image_path) {
                return Storage::url($this->hero_image_path);
            }
            return null;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['hero_image_url'] = $this->hero_image_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        return route('blog.article.page', ['blogArticleSlug' => $this->slug]);
    }
}
