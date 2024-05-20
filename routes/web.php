<?php

use App\Http\Actions\Auth\ConfirmEmailAction;
use App\Http\Actions\Auth\ConfirmEmailResendAction;
use App\Http\Actions\Auth\ForgotPasswordAction;
use App\Http\Actions\Auth\LogoutAction;
use App\Http\Actions\Auth\Pages\ShowConfirmationEmailResendPageAction;
use App\Http\Actions\Auth\Pages\ShowForgotPasswordPageAction;
use App\Http\Actions\Auth\Pages\ShowSignInPageAction;
use App\Http\Actions\Auth\Pages\ShowSignUpPageAction;
use App\Http\Actions\Auth\SignInAction;
use App\Http\Actions\Auth\SignUpAction;
use App\Http\Actions\Blog\Pages\ShowBlogArticlePageAction;
use App\Http\Actions\Blog\Pages\ShowBlogMainPageAction;
//use App\Http\Actions\Store\Work\ShowWorkPageAction;
use App\Http\Actions\EmailSubscription\ConfirmSubscriptionAction;
use App\Http\Actions\EmailSubscription\SubscribeEmailAction;
use App\Http\Actions\Locale\ChangeLocaleAction;
use App\Http\Actions\StaticData\GetStaticDataScript;
use App\Http\Actions\Store\Brand\Pages\ShowBrandPageAction;
use App\Http\Actions\Store\Brand\Pages\ShowBrandSearchPageAction;
use App\Http\Actions\Store\Brand\Pages\ShowBrandsListPageAction;
//use App\Http\Actions\Store\Calculator\CalculateCountOfProductsAction;
//use App\Http\Actions\Store\Calculator\Pages\ShowCalculatorPageAction;
use App\Http\Actions\Store\Cart\AddProductToCartAction;
use App\Http\Actions\Store\Cart\AddSubProductToCartAction;
use App\Http\Actions\Store\Cart\AddPromoCodeToCartAction;
use App\Http\Actions\Store\Cart\ChangeProductCountInCartAction;
use App\Http\Actions\Store\Cart\DeleteProductFromCartAction;
use App\Http\Actions\Store\Cart\GetProductsInCartWithSummaryAction;
use App\Http\Actions\Store\Cart\GetProductsSummaryWithDelivery;
use App\Http\Actions\Store\Cart\Pages\ShowCartPageAction;
use App\Http\Actions\Store\Catalog\GetProductsCountByFilterAction;
use App\Http\Actions\Store\Catalog\GetAllProductsCountByFilterAction;
use App\Http\Actions\Store\Catalog\Pages\ShowCatalogCategoryPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowCatalogPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowAllProductsFilterPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowAllProductsPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowFilterGroupPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductByBrandPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductByColorPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductTypeByColorPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductByFieldPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductByDiscountPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductByAvailabilityPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowProductDoorsByAvailabilityPageAction;
use App\Http\Actions\Store\Catalog\Pages\ShowCatalogRuckyAvailabilityPageAction;
use App\Http\Actions\Store\Checkout\CheckoutConfirmOrderAction;
use App\Http\Actions\Store\Checkout\Pages\ShowCheckoutPage;
use App\Http\Actions\Store\Checkout\Pages\ShowCheckoutThankYouPageAction;
//use App\Http\Actions\Store\Delivery\GetMeestCitiesAction;
//use App\Http\Actions\Store\Delivery\GetMeestDepartmentsAction;
use App\Http\Actions\Store\Delivery\GetNPCitiesAction;
use App\Http\Actions\Store\Delivery\GetNpDepartmentsAction;
use App\Http\Actions\Store\Delivery\GetSATCitiesAction;
use App\Http\Actions\Store\Delivery\GetSATDepartmentsAction;
use App\Http\Actions\Store\DeliveryPage\Pages\ShowDeliveryPageAction;
use App\Http\Actions\Store\AboutUsPage\Pages\ShowAboutUsPageAction;
use App\Http\Actions\Store\Contacts\Pages\ShowContactsPageAction;
use App\Http\Actions\Store\Home\Pages\ShowHomePageAction;
use App\Http\Actions\Store\Payment\Pages\ShowGoToPaymentPageAction;
use App\Http\Actions\Store\Payment\UpdateOrderPaymentStatusAction;
use App\Http\Actions\Store\Product\GetSimilarProductsPaginatedAction;
use App\Http\Actions\Store\Product\Pages\ShowProductPageAction;
use App\Http\Actions\Store\Product\SearchProductAction;
use App\Http\Actions\Store\Seo\ShowRobotsTxtFileContent;
use App\Http\Actions\Store\ServicesPage\Pages\ShowServicesPageAction;
use App\Http\Actions\Store\StaticPage\Pages\ShowStaticPagePageAction;
use App\Http\Actions\Store\VisitRequests\CreateVisitRequestAction;
use App\Http\Actions\Store\WishList\Pages\ShowWishListByTokenPageAction;
use App\Http\Actions\Store\WishList\Pages\ShowWishListPageAction;
use App\Http\Actions\Store\WishList\ProductAddToWishListAction;
use App\Http\Actions\Store\WishList\ProductRemoveFromWishListAction;
use App\Http\Actions\UserProfile\Pages\ShowPasswordEditPageAction;
use App\Http\Actions\UserProfile\Pages\ShowProfileEditPageAction;
use App\Http\Actions\UserProfile\PasswordEditAction;
use App\Http\Actions\UserProfile\ProfileEditAction;
use App\Http\Middleware\AuthenticatedOnly;
use App\Http\Middleware\NotAuthenticatedOnly;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Support\Facades\Route;
use App\Http\Actions\Store\Mail\UserChooseDoorsAction;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$optionalLanguageRoutes = function () {
    /**
     * Auth routes
     */
    Route::prefix('auth')->group(function () {
        //not authed only
        Route::middleware([
            NotAuthenticatedOnly::class,
        ])->group(function () {
            //sign in
            Route::name('auth.sign-in.page')->get('sign-in', ShowSignInPageAction::class);
            Route::name('auth.sign-in')->post('sign-in', SignInAction::class);

            //sign up
            Route::name('auth.sign-up.page')->get('sign-up', ShowSignUpPageAction::class);
            Route::name('auth.sign-up')->post('sign-up', SignUpAction::class);

            //forgot password
            Route::name('auth.forgot-password.page')->get('forgot-password', ShowForgotPasswordPageAction::class);
            Route::name('auth.forgot-password')->post('forgot-password', ForgotPasswordAction::class);

            //confirm email
            Route::name('auth.confirm-email')->get('confirm-email', ConfirmEmailAction::class);
            Route::name('auth.confirm-email-resend.page')->get('resend-confirm-email', ShowConfirmationEmailResendPageAction::class);
            Route::name('auth.confirm-email-resend')->post('resend-confirm-email', ConfirmEmailResendAction::class);
        });


        Route::middleware([
            AuthenticatedOnly::class,
        ])->group(function () {

            //logout
            Route::name('auth.logout')->post('log-out', LogoutAction::class);
        });

    });

    /**
     * User profile routes
     */
    Route::prefix('profile')
        ->middleware([
            AuthenticatedOnly::class,
        ])->group(function () {
            //edit profile
            Route::name('profile.edit.page')->get('edit', ShowProfileEditPageAction::class);
            Route::name('profile.edit')->post('edit', ProfileEditAction::class);

            //change password
            Route::name('profile.edit-password.page')->get('password', ShowPasswordEditPageAction::class);
            Route::name('profile.edit-password')->post('password', PasswordEditAction::class);
        });

    /**
     * Shop routes
     */

    Route::name('store.home')->get('/', ShowHomePageAction::class);

    Route::name('store.services')->get('/services', ShowServicesPageAction::class);
    Route::name('store.delivery-info')->get('/delivery-info', ShowDeliveryPageAction::class);
    Route::name('store.about-us')->get('/about-us', ShowAboutUsPageAction::class);
    Route::name('store.contacts')->get('/contacts', ShowContactsPageAction::class);
    Route::name('store.catalog-by-brand.page')->get('/product-category/brand/{brand}/', ShowProductByBrandPageAction::class);
    Route::name('store.products-by-color.page')->get('/product-category/color/{color}/', ShowProductByColorPageAction::class);
    Route::name('store.product-type-by-color.page')->get('/product-category/{productTypeSlug}/color/{color}/', ShowProductTypeByColorPageAction::class);
    Route::name('store.products-by-field.page')->get('/product-category/field/{productField}/{productOptionID}/', ShowProductByFieldPageAction::class);
    Route::name('store.products-by-discount.page')->get('/product-category/discount/', ShowProductByDiscountPageAction::class);
    Route::name('store.products-by-availability.page')->get('/product-category/available/', ShowProductByAvailabilityPageAction::class);
    Route::name('store.products-doors-by-availability.page')->get('/product-category/available-doors/', ShowProductDoorsByAvailabilityPageAction::class);
    Route::name('store.products-rucky-by-availability.page')->get('/product-category/available-rucky/{productTypeSlug}/{categorySlug}', ShowCatalogRuckyAvailabilityPageAction::class);

    Route::name('store.choose.doors')->post('/user-choose-doors', UserChooseDoorsAction::class);
//    Route::name('store.choose.doors')->middleware('throttle:3,10')->post('/user-choose-doors', UserChooseDoorsAction::class);


    Route::prefix('/shop')->group(function() {
        Route::name('store.all-products.page')->get('/', ShowAllProductsPageAction::class);
        Route::name('store.all-products.filter.page')->get('/filter/{catalogFiltersString?}', ShowAllProductsFilterPageAction::class);
        Route::name('store.all-products.by.filters')->get('/all-filtered-count/{catalogFiltersString?}', GetAllProductsCountByFilterAction::class);
    });

    // Product Types
    Route::prefix('product-category/{productTypeSlug}')->group(function() {
        Route::name('store.catalog.page')->get('/', ShowCatalogPageAction::class);
        Route::name('store.catalog.filter-group.page')->get('/{filterGroupSlug}', ShowFilterGroupPageAction::class);
        Route::name('store.catalog.filter.page')->get('/filter/{catalogFiltersString?}', ShowCatalogPageAction::class);


        Route::name('store.catalog-category.page')->get('/category/{categorySlug}', ShowCatalogCategoryPageAction::class);
        Route::name('store.catalog-category.filter.page')->get('/category/{categorySlug}/filter/{catalogFiltersString?}', ShowCatalogCategoryPageAction::class);

        Route::name('store.catalog.products.by.filters')->get('/filtered-count/{catalogFiltersString?}', GetProductsCountByFilterAction::class);
    });

    Route::prefix('product')->group(function () {
        Route::name('store.product.search')->post('/search', SearchProductAction::class);
        Route::name('store.product.page')->get('/{productSlug}', ShowProductPageAction::class);
        Route::name('store.product.similar-products')->get('/{productSlug}/similar', GetSimilarProductsPaginatedAction::class);
    });

    Route::prefix('wishList')
        ->middleware([
            AuthenticatedOnly::class,
        ])
        ->group(function () {
            Route::name('store.wishlist.private.page')->get('/', ShowWishListPageAction::class);
            Route::name('store.wishlist.private.add-product')->post('product/{productSlug}/add', ProductAddToWishListAction::class);
            Route::name('store.wishlist.private.delete-product')->post('product/{productSlug}/delete', ProductRemoveFromWishListAction::class);
        });

    Route::prefix('wishList')->group(function () {
        Route::name('store.wishlist.public')->get('/{wishListAccessToken}', ShowWishListByTokenPageAction::class);
    });

    Route::prefix('cart')->group(function () {
        Route::name('store.cart.page')->get('/', ShowCartPageAction::class);

        Route::name('store.cart.add-product')->post('product/{productSlug}/add', AddProductToCartAction::class);
        Route::name('store.cart.add-sub-product')->post('sub-product/{productSlug}/add', AddSubProductToCartAction::class);
        Route::name('store.cart.change-product-count')->post('product{productSlug}/update', ChangeProductCountInCartAction::class);
        Route::name('store.cart.delete-product')->post('product/{productSlug}/delete', DeleteProductFromCartAction::class);
        Route::name('store.cart.products-with-summary')->get('product',GetProductsInCartWithSummaryAction::class);
        Route::name('store.cart.add-promo-code')->post('promo',AddPromoCodeToCartAction::class);
    });

    Route::name('static-data.script')->get('static-data.js', GetStaticDataScript::class);


    // TODO: Product adding to cart was broken when I deleted these
    Route::prefix('emailSubscription')->group(function () {
        Route::name('email-subscription.subscribe')->post('/subscribe', SubscribeEmailAction::class);
        Route::name('email-subscription.confirm')->get('/confirm/{emailSubscriptionCode}', ConfirmSubscriptionAction::class);
    });

    Route::prefix('checkout')->group(function () {
        Route::name('store.checkout.page')->get('/', ShowCheckoutPage::class);
        Route::name('store.checkout.confirm')->post('/confirm', CheckoutConfirmOrderAction::class);
        Route::name('store.checkout.thank-you')->get('{order}/thank', ShowCheckoutThankYouPageAction::class);
    });

    // TODO:: Do we need brands pages?
    Route::prefix('brands')->group(function () {
        Route::name('store.brands.list.page')->get('/list/{letter?}', ShowBrandsListPageAction::class);
        Route::name('store.brand.search.page')->get('/search', ShowBrandSearchPageAction::class);
        Route::name('store.brand.page')->get('/{brandSlug}', ShowBrandPageAction::class);
    });


    // TODO:: remove when finish
    /*Route::prefix('calculator')->group(function () {
        Route::name('store.calculator.page')->get('/{productSlug?}', ShowCalculatorPageAction::class);
        Route::name('store.calculator.calculate')->post('/calculate', CalculateCountOfProductsAction::class);
    });*/

    Route::prefix('page')->group(function () {
        Route::name('store.static-page.page')->get('/{staticPageSlug}', ShowStaticPagePageAction::class);
    });

    Route::prefix('blog')->group(function () {
        Route::name('blog.main.page')->get('/', ShowBlogMainPageAction::class);
        Route::name('blog.article.page')->get('/article/{blogArticleSlug}', ShowBlogArticlePageAction::class);
    });

    // TODO: I was told to hide this route
    /*Route::prefix('works')->group(function () {
        Route::name('store.works.page')->get('/', ShowWorkPageAction::class);
    });*/

    Route::prefix('visitRequest')->group(function () {
        Route::name('store.visit-request.create')->post('/create', CreateVisitRequestAction::class);
    });

    Route::prefix('payment')->group(function () {
       Route::name('store.payment.page')->get('/{order}', ShowGoToPaymentPageAction::class);
    });

    Route::prefix('delivery')->group(function () {
        Route::prefix('np')->group(function () {
            Route::name('delivery.np.cities')->get('/cities', GetNPCitiesAction::class);
            Route::name('delivery.np.departments')->get('/departments', GetNpDepartmentsAction::class);
        });

        Route::prefix('sat')->group(function () {
            Route::name('delivery.sat.cities')->get('/cities', GetSATCitiesAction::class);
            Route::name('delivery.sat.departments')->get('/departments', GetSATDepartmentsAction::class);
        });

        // TODO:: remove when finish
        /*Route::prefix('meest')->group(function () {
            Route::name('delivery.meest.cities')->get('/cities', GetMeestCitiesAction::class);
            Route::name('delivery.meest.departments')->get('/departments', GetMeestDepartmentsAction::class);
        });*/

    });
};

Route::prefix('/{lang}/')
    ->whereIn('lang', app()->make(ApplicationConfigService::class)->getAvailableLanguages())
    ->middleware(['set.locale', 'check.locale'])
    ->group($optionalLanguageRoutes);

Route::middleware(['check.locale'])->group($optionalLanguageRoutes);

Route::name('locale.change')->get('/change-locale/{newLocale}', ChangeLocaleAction::class);

Route::prefix('cart')->group(function () {
    Route::name('store.cart.summary-with-delivery')->get('summary-with-delivery', GetProductsSummaryWithDelivery::class);
});

Route::prefix('payment')->group(function () {
    Route::name('payment.update-payment-status')->get('/update/{order}', UpdateOrderPaymentStatusAction::class);
});

Route::name('robots.txt.content')->get('robots.txt', ShowRobotsTxtFileContent::class);
