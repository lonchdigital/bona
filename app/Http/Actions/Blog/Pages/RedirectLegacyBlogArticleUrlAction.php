<?php

namespace App\Http\Actions\Blog\Pages;

use App\Helpers\MultiLangRoute;
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
        return redirect(
            MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $legacyBlogArticleSlug]),
            301
        );
    }
}
