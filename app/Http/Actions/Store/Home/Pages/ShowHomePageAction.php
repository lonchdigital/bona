<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Models\User;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\ProductCategory\CategoryService;
use App\Services\HomePage\HomePageService;
use App\Services\Brand\BrandService;
/*use App\Services\WishList\WishListService;*/

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        HomePageService $homePageService,
//        WishListService $wishListService,
        CurrencyService $currencyService,
        BrandService $brandService,
        BlogArticleService $blogArticleService,
    )
    {
//        $productType->load(['fields', 'fields.options']);

        $categoryService = app()->make(CategoryService::class);

        /*$wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }*/

//        $instagramFeed = \Dymantic\InstagramFeed\InstagramFeed::for('bonadoors', 6);

        $profile = \Dymantic\InstagramFeed\Profile::for('bonadoors');
        $instagramFeed = $profile?->feed();

        return view('pages.store.home', [
            'config' => $homePageService->getHomePageConfig(),
            'slides' => $homePageService->getHomePageSlides(),
            'brands' => $brandService->getBrands(),
            'productTypes' => $homePageService->getHomePageProductTypes(),
            'specificProductTypes' => $homePageService->getSpecificProductTypes(),
            'homeNewProducts' => $homePageService->getHomePageNewProducts(),
            'homeBestSalesProducts' => $homePageService->getHomePageBestSalesProducts(),
            'homeTestimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoTextByLanguage(app()->getLocale()),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'articles' => $blogArticleService->getLatestArticles(3),
            'instagramFeed' => $instagramFeed,

//            'newProducts' => $homePageService->getNewProducts(),
//            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
//            'fieldFilterString' => $homePageService->getProductsCustomFieldOptionsName(),
//            'productsByField' => $homePageService->getProductsByCustomFieldOptions(),
        ]);
    }
}
