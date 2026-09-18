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
        string $blogArticleSlug,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
        AuthorService $authorService,
        SerpAgentHtmlService $htmlService,
    ) {
        $locale = app()->getLocale();
        $resolvedArticle = BlogArticle::resolveLocalizedSlug($blogArticleSlug, $locale);

        abort_unless($resolvedArticle, 404);

        $blogArticle = $resolvedArticle['article'];
        abort_unless($blogArticle->hasLocaleVersion($locale), 404);

        if ($resolvedArticle['should_redirect']) {
            return redirect($blogArticle->urlForLocale($locale), 301);
        }

        $blogArticle->meta_tags = $this->handleFollowTag($blogArticle->meta_tags);
        $blogArticle->loadMissing('blocks');
        LastModified::set($blogArticle->updated_at);

        $latestArticles = $blogArticleService->getLatestArticlesExceptCurrent($blogArticle->id);

        // Further reading lists articles, the resource block lists
        // destinations, and neither repeats what the other already shows.
        [$articleRecommendedLinks, $articleUsefulLinks] = $blogArticleService->splitEditorialLinks(
            $blogArticleService->extractEditorialLinks($blogArticle, $locale, 'related'),
            $blogArticleService->extractEditorialLinks($blogArticle, $locale, 'resources'),
            $locale,
        );

        // The grid at the foot of the page is the fallback for further
        // reading, so an article named there is not named twice.
        $latestArticles = $latestArticles->reject(
            fn (BlogArticle $article) => $blogArticleService->linksCoverArticle($articleRecommendedLinks, $article, $locale)
        )->values();

        $articleBlocks = $blogArticle->blocks->map(function ($block) use ($blogArticleService, $htmlService, $locale) {
            $content = is_array($block->content) ? $block->content : [];

            if ($block->type_id === BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                $content[$locale] = $htmlService->decorateForDisplay(
                    $blogArticleService->stripEditorialLinkSections((string) ($content[$locale] ?? '')),
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
            'latestArticles' => $latestArticles,
            'articleRecommendedLinks' => $articleRecommendedLinks,
            'articleUsefulLinks' => $articleUsefulLinks,
            // Null until an author is created in the admin panel; the template
            // then falls back to the loose author fields in the global config.
            'articleAuthor' => $authorService->getDefaultAuthor(),
            'articleFaq' => $blogArticleService->extractFaq($blogArticle, app()->getLocale()),
            'seoAlternateLocales' => $blogArticle->availableLocales(),
            'seoAlternateLinks' => $blogArticle->alternateLinks(),
        ]);
    }
}
