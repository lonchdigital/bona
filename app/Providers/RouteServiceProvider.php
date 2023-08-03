<?php

namespace App\Providers;

use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\WishList;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            Route::bind('productTypeSlug', function (string $slug) {
                return ProductType::where('slug', $slug)->firstOrFail();
            });

            Route::bind('productSlug', function (string $slug) {
                return Product::where('slug', $slug)->firstOrFail();
            });

            Route::bind('wishListAccessToken', function (string $token) {
                return WishList::where('access_token', $token)->firstOrFail();
            });

            Route::bind('categorySlug', function (string $slug) {
                return Category::where('slug', $slug)->firstOrFail();
            });

            Route::bind('brandSlug', function (string $slug) {
                return Brand::where('slug', $slug)->firstOrFail();
            });

            Route::bind('collectionSlug', function (string $slug) {
                return Collection::where('slug', $slug)->firstOrFail();
            });

            Route::bind('blogCategorySlug', function (string $slug) {
                return BlogCategory::where('slug', $slug)->firstOrFail();
            });

            Route::bind('blogArticleSlug', function (string $slug) {
                return BlogArticle::where('slug', $slug)->firstOrFail();
            });

            Route::bind('filterGroupSlug', function (string $slug) {
                return FilterGroup::where('slug', $slug)->firstOrFail();
            });

            Route::bind('lang', function (string $lang) {
                return $lang;
            });



            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'))
                ->group(base_path('routes/admin.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
