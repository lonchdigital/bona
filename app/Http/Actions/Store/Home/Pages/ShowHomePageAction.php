<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\HomePage\HomePageService;
use App\Services\WishList\WishListService;

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        HomePageService $homePageService,
        WishListService $wishListService,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
    )
    {
        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }

        return view('pages.store.home', [
            'config' => $homePageService->getHomePageConfig(),
            'slides' => $homePageService->getHomePageSlides(),
            'brands' => $homePageService->getHomePageBrands(),
            'newProducts' => $homePageService->getNewProducts(),
            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'fieldFilterString' => $homePageService->getProductsCustomFieldOptionsName(),
            'productsByField' => $homePageService->getProductsByCustomFieldOptions(),
            'articles' => $blogArticleService->getLatestArticles(3),
        ]);
    }
}
