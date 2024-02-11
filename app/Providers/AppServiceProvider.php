<?php

namespace App\Providers;

use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Application\ApplicationConfigService;
use App\Services\Cart\CartService;
use App\Services\Locale\LocaleService;
//use App\Services\WishList\WishListService;
//use App\Services\Brand\BrandService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Services\Contacts\ContactsPageService;

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
        ApplicationConfigService $applicationService,
        CartService              $cartService,
        ContactsPageService      $contactsService

        // TODO: remove WishListService and BrandService if we do not need them
        //WishListService          $wishListService,
        //BrandService             $brandService,
    ): void
    {
        view()->composer(
            [
                'layouts.admin-main',
                'components.cart-window',
            ],
            function ($view) use ($productTypeService) {
                $view->with('productTypes', $productTypeService->getProductTypes());
            }
        );

        view()->composer(
            [
                'layouts.store-main',
            ],
            function ($view) use ($productTypeService, $contactsService) {
                $view->with('productTypes', $productTypeService->getSortedProductTypes());
                $view->with('locationService', app()->make(LocaleService::class));
                $view->with('contactsFooter', $contactsService->getContactsFooter());
            }
        );

        view()->composer(
            [
                'layouts.store-main',
                'components.cart-window',
            ],
            function ($view) use ($cartService) {
                $cart = Auth::user() ? $cartService->getCartForAuthUser(Auth::user()) : $cartService->getCartForGuestUser(request()->session()->getId());
                $countOfProductInCart = 0;
                if ($cart) {
                    $countOfProductInCart = $cartService->getCountOfProductsInCart($cart);
                }

                $view->with('countOfProductInCart', $countOfProductInCart);
            }
        );

        // TODO: remove if we do not need it
        /*view()->composer([
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
        });*/

        view()->composer(
            '*',
            function ($view) use ($applicationService) {
                $view->with('availableLanguages', $applicationService->getAvailableLanguages())
                    ->with('applicationGlobalOptions', $applicationService->getAllApplicationConfigOptions())
                    ->with('baseLanguage', config('app.locale'));
            }
        );

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
