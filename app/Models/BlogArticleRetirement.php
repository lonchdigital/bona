<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class BlogArticleRetirement extends Model
{
    protected $guarded = [];

    protected $casts = ['retired_at' => 'datetime', 'restored_at' => 'datetime'];

    public function article()
    {
        return $this->belongsTo(BlogArticle::class, 'blog_article_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('restored_at');
    }

    /** What the old address answers: a permanent redirect, or 410 when there is nowhere to send it. */
    public function response(): Response
    {
        return $this->redirect_to
            ? redirect()->away($this->redirect_to, 301)
            : response()->view('default.404', [], 410);
    }
}
