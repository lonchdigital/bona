<?php

namespace App\Providers;

use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Application\ApplicationConfigService;
use App\Services\Cart\CartService;
use App\Services\Locale\LocaleService;
//use App\Services\WishList\WishListService;
//use App\Services\Brand\BrandService;
use Illuminate\Support\Facades\Cache;
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

        $mainProductTypes = Cache::remember('mainProductTypes', 43200, function () use ($productTypeService) {
            return $productTypeService->getProductTypes();
        });

        view()->composer(
            [
                'layouts.admin-main',
                'components.cart-window',
            ],
            function ($view) use ($mainProductTypes) {
                $view->with('productTypes', $mainProductTypes);
            }
        );

        $sortedProductTypes = Cache::remember('sortedProductTypes', 43200, function () use ($productTypeService) {
            return $productTypeService->getSortedProductTypes();
        });
        $contactsFooter = Cache::remember('contactsFooter', 43200, function () use ($contactsService) {
            return $contactsService->getContactsFooter();
        });


        view()->composer(
            [
                'layouts.store-main',
            ],
            function ($view) use ($sortedProductTypes, $contactsFooter) {
                $view->with('productTypes', $sortedProductTypes);
                $view->with('locationService', app()->make(LocaleService::class));
                $view->with('contactsFooter', $contactsFooter);
            }
        );

        view()->composer(
            [
                'layouts.store-main',
                'components.cart-window',
            ],
            function ($view) use ($cartService) {
                $countOfProductInCart = 0;
                if (request()->hasSession() && request()->session()) { // Проверяем доступность и наличие сессии
                    $cart = Auth::user() ? $cartService->getCartForAuthUser(Auth::user()) : $cartService->getCartForGuestUser(request()->session()->getId());
                    if ($cart) {
                        $countOfProductInCart = $cartService->getCountOfProductsInCart($cart);
                    }
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

        $applicationGlobalOptions = Cache::remember('applicationGlobalOptions', 43200, function () use ($applicationService) {
            return $applicationService->getAllApplicationConfigOptions();
        });

        $availableLanguages = $applicationService->getAvailableLanguages();

        view()->composer(
            '*',
            function ($view) use ($applicationService, $applicationGlobalOptions, $availableLanguages) {
                $view->with('availableLanguages', $availableLanguages)
                    ->with('applicationGlobalOptions', $applicationGlobalOptions)
                    ->with('baseLanguage', config('app.locale'));
            }
        );

        /*if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }*/
    }
}
