<?php

namespace App\Http\Actions\Store\Author;

use Abordage\LastModified\Facades\LastModified;
use App\Models\Author;
use App\Services\BlogArticle\BlogArticleService;

class ShowAuthorPageAction
{
    public function __invoke(Author $author, BlogArticleService $blogArticleService)
    {
        LastModified::set($author->updated_at);

        return view('pages.store.author-page', [
            'author' => $author->load('certificates'),
            // The site has a single author for now, so every article on the
            // blog is theirs.
            'authorArticles' => $blogArticleService->getLatestArticles(6),
        ]);
    }
}
