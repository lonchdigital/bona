<?php

namespace App\Providers;

use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Application\ApplicationConfigService;
use App\Services\Cart\CartService;
use App\Services\Cart\GuestCartToken;
use App\Services\CatalogMenu\CatalogMenuService;
use App\Services\Contacts\ContactsPageService;
use App\Services\Locale\LocaleService;
// use App\Services\WishList\WishListService;
// use App\Services\Brand\BrandService;
use App\Services\WishList\GuestWishListToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Shared for the request: a token issued now is not readable back from
        // the cookie until the next one, so the instance has to remember it.
        $this->app->singleton(GuestWishListToken::class);
        $this->app->singleton(GuestCartToken::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(
        ProductTypeService $productTypeService,
        CatalogMenuService $catalogMenuService,
        ApplicationConfigService $applicationService,
        CartService $cartService,
        ContactsPageService $contactsService

        // TODO: remove WishListService and BrandService if we do not need them
        // WishListService          $wishListService,
        // BrandService             $brandService,
    ): void {

        /*
         * These used to be read here, in boot(), which runs before anything
         * else on every request and every console command alike. That made the
         * application unable to start against a database that has no tables
         * yet — `migrate` could not run on a fresh checkout, because booting it
         * queried tables the migration was about to create.
         *
         * Read inside the composer instead: the query happens when a layout
         * that needs it is actually rendered, and console commands never touch
         * the database to start.
         */
        view()->composer(
            [
                'layouts.admin-main',
                'components.cart-window',
            ],
            function ($view) use ($productTypeService) {
                $view->with('productTypes', Cache::remember('mainProductTypes', 43200, function () use ($productTypeService) {
                    return $productTypeService->getProductTypes();
                }));
            }
        );

        view()->composer(
            [
                'layouts.store-main',
            ],
            function ($view) use ($catalogMenuService, $contactsService) {
                $productTypes = $catalogMenuService->getStorefrontProductTypes();

                $view->with('productTypes', $productTypes);
                $view->with('locationService', app()->make(LocaleService::class));
                $view->with('contactsFooter', Cache::remember('contactsFooter', 43200, function () use ($contactsService) {
                    return $contactsService->getContactsFooter();
                }));
            }
        );

        view()->composer(
            [
                'layouts.store-main',
                'components.cart-window',
            ],
            function ($view) use ($cartService) {
                $countOfProductInCart = 0;
                if (Auth::user()) {
                    $cart = $cartService->getCartForAuthUser(Auth::user());
                } else {
                    $token = app(GuestCartToken::class)->existing();
                    $cart = $token ? $cartService->getCartForGuestUser($token) : null;
                }

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
                    ->with('applicationGlobalOptions', Cache::remember('applicationGlobalOptions', 43200, function () use ($applicationService) {
                        return $applicationService->getAllApplicationConfigOptions();
                    }))
                    ->with('baseLanguage', config('app.locale'));
            }
        );

        /*if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }*/
    }
}
