<?php

namespace App\Http\Actions\Store\Home\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use App\Services\ProductCategory\CategoryService;
use App\Services\HomePage\HomePageService;
use App\Services\WishList\WishListService;

class ShowHomePageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        HomePageService $homePageService,
//        WishListService $wishListService,
        CurrencyService $currencyService,
//        BlogArticleService $blogArticleService,
    )
    {
//        $productType->load(['fields', 'fields.options']);

        $categoryService = app()->make(CategoryService::class);

        /*$wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }*/

        $productTypeDoors = $productType->find(3);

        return view('pages.store.home', [
            'config' => $homePageService->getHomePageConfig(),
            'slides' => $homePageService->getHomePageSlides(),
//            'brands' => $homePageService->getHomePageBrands(),
//            'categories' => $categoryService->getProductCategories($productTypeDoors),
            'productTypes' => $homePageService->getProductTypes(),
//            'productTypeDoors' => $productTypeDoors,
            'homeNewProducts' => $homePageService->getHomePageNewProducts(),
            'homeBestSalesProducts' => $homePageService->getHomePageBestSalesProducts(),
            'homeTestimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoTextByLanguage(app()->getLocale()),
            'baseCurrency' => $currencyService->getBaseCurrency(),

//            'newProducts' => $homePageService->getNewProducts(),
//            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
//            'fieldFilterString' => $homePageService->getProductsCustomFieldOptionsName(),
//            'productsByField' => $homePageService->getProductsByCustomFieldOptions(),
//            'articles' => $blogArticleService->getLatestArticles(3),
        ]);
    }
}
