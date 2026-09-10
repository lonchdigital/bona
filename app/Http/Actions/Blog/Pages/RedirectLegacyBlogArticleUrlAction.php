<?php

namespace App\Http\Actions\Blog\Pages;

use App\Models\BlogArticle;
use Illuminate\Http\RedirectResponse;

class RedirectLegacyBlogArticleUrlAction
{
    /**
     * Articles used to live under /blog/article/{slug} and have been indexed
     * under that URL since 2023, so it answers with a permanent redirect to
     * the current /blog/{slug} instead of a 404.
     *
     * The slug is deliberately not model bound: an unknown slug should land on
     * the article page and 404 there, keeping a single place that decides what
     * exists.
     */
    public function __invoke(string $legacyBlogArticleSlug): RedirectResponse
    {
        $locale = app()->getLocale();
        $resolvedArticle = BlogArticle::resolveLocalizedSlug($legacyBlogArticleSlug, $locale);

        abort_unless($resolvedArticle && $resolvedArticle['article']->hasLocaleVersion($locale), 404);

        return redirect($resolvedArticle['article']->urlForLocale($locale), 301);
    }
}
