<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\HomePageConfig;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\HomePage\HomePageService;
use App\Support\LastModified;

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        HomePageService $homePageService,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
    ) {
        /*
         * Absent on a fresh install, and the page read straight through it —
         * so the front page answered 500 rather than showing itself with
         * nothing filled in yet.
         */
        $config = $homePageService->getHomePageConfig() ?? new HomePageConfig;
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.home', [
            'config' => $config,
            'slides' => $homePageService->getHomePageSlides(),
            //            'brands' => $brandService->getBrands(), // get all brands
            'brands' => $homePageService->getHomePageBrands(), // get selected brands for homepage
            'catalogCards' => $homePageService->getHomePageCatalogCards(json_decode($config->product_types ?? '[]', true)),
            'styleSection' => $homePageService->getHomePageStyleSection(),
            'homeSections' => $homePageService->getHomePageContentSections(),
            'specificProductTypes' => $homePageService->getSpecificProductTypes(),
            'homePopularProducts' => $homePageService->getHomePagePopularProducts(),
            'homeTestimonials' => $homePageService->getStorefrontTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoTextByLanguage(app()->getLocale()),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'articles' => $blogArticleService->getLatestArticles(3),
            'instagramFeed' => $homePageService->getInstagramFeed(),
        ]);
    }
}
