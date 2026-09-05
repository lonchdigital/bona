<?php

namespace App\Http\Actions\Blog\Pages;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogArticle;
use App\Services\Author\AuthorService;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\SerpAgent\SerpAgentHtmlService;
use App\Support\LastModified;

class ShowBlogArticlePageAction extends BaseAction
{
    public function __invoke(
        BlogArticle $blogArticle,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
        AuthorService $authorService,
        SerpAgentHtmlService $htmlService,
    ) {
        $blogArticle->meta_tags = $this->handleFollowTag($blogArticle->meta_tags);
        $blogArticle->loadMissing('blocks');
        LastModified::set($blogArticle->updated_at);

        $locale = app()->getLocale();
        $articleBlocks = $blogArticle->blocks->map(function ($block) use ($htmlService, $locale) {
            $content = is_array($block->content) ? $block->content : [];

            if ($block->type_id === BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                $content[$locale] = $htmlService->decorateForDisplay(
                    (string) ($content[$locale] ?? ''),
                    $locale,
                );
            }

            return [
                'type_id' => (int) $block->type_id,
                'content' => $content,
            ];
        });

        return view('pages.blog.article', [
            'blogArticle' => $blogArticle,
            'articleBlocks' => $articleBlocks,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'latestArticles' => $blogArticleService->getLatestArticlesExceptCurrent($blogArticle->id),
            // Null until an author is created in the admin panel; the template
            // then falls back to the loose author fields in the global config.
            'articleAuthor' => $authorService->getDefaultAuthor(),
            'articleFaq' => $blogArticleService->extractFaq($blogArticle, app()->getLocale()),
        ]);
    }
}
