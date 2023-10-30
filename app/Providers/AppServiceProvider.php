<?php

namespace App\Providers;

use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Admin\ProductSubtype\ProductSubtypeService;
use App\Services\Application\ApplicationConfigService;
use App\Services\Brand\BrandService;
use App\Services\Cart\CartService;
use App\Services\WishList\WishListService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(
        ProductTypeService       $productTypeService,
        ProductSubtypeService    $productSubtypeService,
        ApplicationConfigService $applicationService,
        CartService              $cartService,
        BrandService             $brandService,
        WishListService          $wishListService,
    ): void
    {
        view()->composer(
            [
                'layouts.admin-main',
                'components.cart-window',
            ],
            function ($view) use ($productTypeService, $productSubtypeService) {
                $view->with('productTypes', $productTypeService->getProductTypes());
                $view->with('productSubtypes', $productSubtypeService->getProductSubtypes());
            }
        );

        view()->composer(
            [
                'layouts.store-main',
            ],
            function ($view) use ($productTypeService) {
                $view->with('productTypes', $productTypeService->getProductTypes());
            }
        );

        view()->composer(
            [
                'layouts.store-main',
                'components.cart-window',
            ],
            function ($view) use ($cartService) {
                $cart = Auth::user() ? $cartService->getCartForAuthUser(Auth::user()) : $cartService->getCartForGuestUser(session_id());
                $countOfProductInCart = 0;
                if ($cart) {
                    $countOfProductInCart = $cartService->getCountOfProductsInCart($cart);
                }

                $view->with('countOfProductInCart', $countOfProductInCart);
            }
        );

        view()->composer([
            'layouts.store-main',
        ], function ($view) use ($brandService, $wishListService) {
            $user = Auth::user();
            $isWishListEmpty = true;

            if ($user) {
                $wishList = $wishListService->getWishListByUser($user);
                $isWishListEmpty = !(bool) count($wishListService->getProductsByWishList($wishList));
            }

            $view->with('brands', $brandService->sortBrandsByFirstLetterByProductType($brandService->getBrands()));
            $view->with('wishlistEmpty', $isWishListEmpty);
        });

        view()->composer(
            '*',
            function ($view) use ($applicationService) {
                $view->with('availableLanguages', $applicationService->getAvailableLanguages())
                    ->with('baseLanguage', config('app.locale'));
            }
        );

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
