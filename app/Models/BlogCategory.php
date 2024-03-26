<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // TODO:: we do not uses categories for Blog
    /*public function toSitemapTag(): Url | string | array
    {
        return route('blog.articles-by-category.page', ['blogCategorySlug' => $this->slug]);
    }*/
}
