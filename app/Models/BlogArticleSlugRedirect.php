<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogArticleSlugRedirect extends Model
{
    protected $guarded = [];

    public function article()
    {
        return $this->belongsTo(BlogArticle::class, 'blog_article_id');
    }
}
