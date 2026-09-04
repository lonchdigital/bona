<?php

namespace App\Providers;

use App\Http\Actions\HealthCheckAction;
use App\Models\Author;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ServicesPageSections;
use App\Models\WishList;
use App\Models\Work;
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
        $this->configureRouteBindings();

        $this->routes(function () {

            Route::get('/up', HealthCheckAction::class)->name('health');

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'))
                ->group(base_path('routes/admin.php'));
        });
    }

    /**
     * Register explicit bindings even when Laravel loads cached routes.
     */
    protected function configureRouteBindings(): void
    {
        Route::bind('productTypeSlug', fn (string $slug) => ProductType::where('slug', $slug)->firstOrFail());
        Route::bind('productSlug', fn (string $slug) => Product::where('slug', $slug)->firstOrFail());
        Route::bind('serviceSlug', fn (string $slug) => ServicesPageSections::where('slug', $slug)->firstOrFail());
        Route::bind('wishListAccessToken', fn (string $token) => WishList::where('access_token', $token)->firstOrFail());
        Route::bind('categorySlug', fn (string $slug) => Category::where('slug', $slug)->firstOrFail());
        Route::bind('brandSlug', fn (string $slug) => Brand::where('slug', $slug)->firstOrFail());
        Route::bind('collectionSlug', fn (string $slug) => Collection::where('slug', $slug)->firstOrFail());
        Route::bind('blogCategorySlug', fn (string $slug) => BlogCategory::where('slug', $slug)->firstOrFail());
        Route::bind('workSlug', fn (string $slug) => Work::where('slug', $slug)->firstOrFail());
        Route::bind('authorSlug', fn (string $slug) => Author::where('slug', $slug)->firstOrFail());
        Route::bind('blogArticleSlug', fn (string $slug) => BlogArticle::where('slug', $slug)->firstOrFail());
        Route::bind('filterGroupSlug', fn (string $slug) => FilterGroup::where('slug', $slug)->firstOrFail());
        Route::bind('lang', fn (string $lang) => $lang);
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
