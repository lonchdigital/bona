<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\HomePage\HomePageService;
use App\Services\Brand\BrandService;
use Abordage\LastModified\Facades\LastModified;

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        HomePageService $homePageService,
        CurrencyService $currencyService,
        BrandService $brandService,
        BlogArticleService $blogArticleService,
    )
    {
//        $productType->load(['fields', 'fields.options']);
//        $categoryService = app()->make(CategoryService::class);


        $profile = \Dymantic\InstagramFeed\Profile::for('bonadoors');
//        $instagramFeed = $profile?->refreshFeed();
        $instagramFeed = $profile?->feed();

       /* if( $profile !== null ) {
            $profile->refreshFeed();
            $instagramFeed = $profile?->feed();
        } else {
            $instagramFeed = [];
        }*/


        $config = $homePageService->getHomePageConfig();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.home', [
            'config' => $config,
            'slides' => $homePageService->getHomePageSlides(),
//            'brands' => $brandService->getBrands(), // get all brands
            'brands' => $homePageService->getHomePageBrands(), // get selected brands for homepage
            'productTypes' => $homePageService->getHomePageProductTypes(json_decode($config->product_types)),
            'specificProductTypes' => $homePageService->getSpecificProductTypes(),
            'homeNewProducts' => $homePageService->getHomePageNewProducts(),
            'homeBestSalesProducts' => $homePageService->getHomePageBestSalesProducts(),
            'homeTestimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoTextByLanguage(app()->getLocale()),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'articles' => $blogArticleService->getLatestArticles(3),
            'instagramFeed' => $instagramFeed,
        ]);
    }
}
