<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\ProductCategory\CategoryService;
use App\Services\HomePage\HomePageService;
use App\Services\WishList\WishListService;
use Dymantic\InstagramFeed\InstagramFeed;

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        HomePageService $homePageService,
//        WishListService $wishListService,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
    )
    {
//        $productType->load(['fields', 'fields.options']);

        $categoryService = app()->make(CategoryService::class);

        /*$wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }*/

//        $instagramFeed = InstagramFeed::for('{instFeed}');


        return view('pages.store.home', [
            'config' => $homePageService->getHomePageConfig(),
            'slides' => $homePageService->getHomePageSlides(),
//            'brands' => $homePageService->getHomePageBrands(),
            'productTypes' => $homePageService->getProductTypes(),
            'homeNewProducts' => $homePageService->getHomePageNewProducts(),
            'homeBestSalesProducts' => $homePageService->getHomePageBestSalesProducts(),
            'homeTestimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoTextByLanguage(app()->getLocale()),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'articles' => $blogArticleService->getLatestArticles(3),
            'instagramFeed' => [],

//            'newProducts' => $homePageService->getNewProducts(),
//            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
//            'fieldFilterString' => $homePageService->getProductsCustomFieldOptionsName(),
//            'productsByField' => $homePageService->getProductsByCustomFieldOptions(),
        ]);
    }
}
