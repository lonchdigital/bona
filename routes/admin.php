<?php

use App\Http\Actions\Admin\BlogArticles\BlogArticleCreateAction;
use App\Http\Actions\Admin\BlogArticles\BlogArticleDeleteAction;
use App\Http\Actions\Admin\BlogArticles\BlogArticleEditAction;
use App\Http\Actions\Admin\BlogArticles\Pages\ShowBlogArticleCreatePageAction;
use App\Http\Actions\Admin\BlogArticles\Pages\ShowBlogArticleEditPageAction;
use App\Http\Actions\Admin\BlogArticles\Pages\ShowBlogArticlesListPageAction;
use App\Http\Actions\Admin\BlogCategories\BlogCategoryCreateAction;
use App\Http\Actions\Admin\BlogCategories\BlogCategoryDeleteAction;
use App\Http\Actions\Admin\BlogCategories\BlogCategoryEditAction;
use App\Http\Actions\Admin\BlogCategories\Pages\ShowBlogCategoriesListPageAction;
use App\Http\Actions\Admin\BlogCategories\Pages\ShowBlogCategoryCreatePageAction;
use App\Http\Actions\Admin\BlogCategories\Pages\ShowBlogCategoryEditPageAction;
use App\Http\Actions\Admin\BlogSlides\BlogSlidesEditAction;
use App\Http\Actions\Admin\BlogSlides\Pages\ShowBlogSlidesEditPageAction;
use App\Http\Actions\Admin\Brands\BrandCreateAction;
use App\Http\Actions\Admin\Brands\BrandDeleteAction;
use App\Http\Actions\Admin\Brands\BrandEditAction;
use App\Http\Actions\Admin\Brands\GetCollectionsByBrandAction;
use App\Http\Actions\Admin\Brands\Pages\ShowBrandCreatePageAction;
use App\Http\Actions\Admin\Brands\Pages\ShowBrandEditPageAction;
use App\Http\Actions\Admin\Brands\Pages\ShowBrandsListPageAction;
use App\Http\Actions\Admin\Collections\CollectionCreateAction;
use App\Http\Actions\Admin\Collections\CollectionDeleteAction;
use App\Http\Actions\Admin\Collections\CollectionEditAction;
use App\Http\Actions\Admin\Collections\Pages\ShowCollectionCreatePageAction;
use App\Http\Actions\Admin\Collections\Pages\ShowCollectionEditPageAction;
use App\Http\Actions\Admin\Collections\Pages\ShowCollectionsListPageAction;
use App\Http\Actions\Admin\Collections\SearchCollectionAction;
use App\Http\Actions\Admin\Colors\ColorCreateAction;
use App\Http\Actions\Admin\Colors\ColorDeleteAction;
use App\Http\Actions\Admin\Colors\ColorEditAction;
use App\Http\Actions\Admin\Colors\Pages\ShowColorCreatePageAction;
use App\Http\Actions\Admin\Colors\Pages\ShowColorEditPageAction;
use App\Http\Actions\Admin\Colors\Pages\ShowColorsListPageAction;
use App\Http\Actions\Admin\Countries\CountryCreateAction;
use App\Http\Actions\Admin\Countries\CountryDeleteAction;
use App\Http\Actions\Admin\Countries\CountryEditAction;
use App\Http\Actions\Admin\Countries\Pages\ShowCountriesListPageAction;
use App\Http\Actions\Admin\Countries\Pages\ShowCountryCreatePageAction;
use App\Http\Actions\Admin\Countries\Pages\ShowCountryEditPageAction;
use App\Http\Actions\Admin\Currencies\CurrencyCreateAction;
use App\Http\Actions\Admin\Currencies\CurrencyDeleteAction;
use App\Http\Actions\Admin\Currencies\CurrencyEditAction;
use App\Http\Actions\Admin\Currencies\Pages\ShowCurrenciesListPageAction;
use App\Http\Actions\Admin\Currencies\Pages\ShowCurrencyCreatePageAction;
use App\Http\Actions\Admin\Currencies\Pages\ShowCurrencyEditPageAction;
use App\Http\Actions\Admin\Dashboard\Pages\ShowDashboardPageAction;
use App\Http\Actions\Admin\HomePage\HomePageEditAction;
use App\Http\Actions\Admin\HomePage\Pages\ShowHomePageEditPageAction;
use App\Http\Actions\Admin\Orders\Pages\ShowOrderDetailsPageAction;
use App\Http\Actions\Admin\Orders\Pages\ShowOrdersListPageAction;
use App\Http\Actions\Admin\Orders\UpdateOrderAction;
use App\Http\Actions\Admin\ProductCategories\CategoryCreateAction;
use App\Http\Actions\Admin\ProductCategories\CategoryDeleteAction;
use App\Http\Actions\Admin\ProductCategories\CategoryEditAction;
use App\Http\Actions\Admin\ProductCategories\Pages\ShowCategoriesListByProductTypePageAction;
use App\Http\Actions\Admin\ProductCategories\Pages\ShowCategoriesListPageAction;
use App\Http\Actions\Admin\ProductCategories\Pages\ShowCategoryCreatePageAction;
use App\Http\Actions\Admin\ProductCategories\Pages\ShowCategoryEditPageAction;
use App\Http\Actions\Admin\ProductFields\Pages\ShowProductFieldCreatePageAction;
use App\Http\Actions\Admin\ProductFields\Pages\ShowProductFieldEditPageAction;
use App\Http\Actions\Admin\ProductFields\Pages\ShowProductFieldsListPageAction;
use App\Http\Actions\Admin\ProductFields\ProductFieldCreateAction;
use App\Http\Actions\Admin\ProductFields\ProductFieldDeleteAction;
use App\Http\Actions\Admin\ProductFields\ProductFieldEditAction;

use App\Http\Actions\Admin\ProductAttributes\Pages\ShowProductAttributesListPageAction;
use App\Http\Actions\Admin\ProductAttributes\Pages\ShowProductAttributeCreatePageAction;
use App\Http\Actions\Admin\ProductAttributes\Pages\ShowProductAttributeEditPageAction;
use App\Http\Actions\Admin\ProductAttributes\ProductAttributeCreateAction;
use App\Http\Actions\Admin\ProductAttributes\ProductAttributeEditAction;
use App\Http\Actions\Admin\ProductAttributes\ProductAttributeDeleteAction;

use App\Http\Actions\Admin\Products\GetParentProductDataAction;
use App\Http\Actions\Admin\Products\GetProductsBySearchAction;
use App\Http\Actions\Admin\Products\GetAllProductsBySearchAction;
use App\Http\Actions\Admin\Products\GetSubProductsBySearchAction;
use App\Http\Actions\Admin\Products\Pages\ShowProductCreatePageAction;
use App\Http\Actions\Admin\Products\Pages\ShowProductEditPageAction;
use App\Http\Actions\Admin\Products\Pages\ShowProductsListPageAction;
use App\Http\Actions\Admin\Products\ProductCreateAction;
use App\Http\Actions\Admin\Products\ProductDeleteAction;
use App\Http\Actions\Admin\Products\ProductEditAction;
use App\Http\Actions\Admin\ProductsImport\DeleteImportedProductsAction;
use App\Http\Actions\Admin\ProductsImport\DeleteProductFromListAction;
use App\Http\Actions\Admin\ProductsImport\DownloadProductsImportExampleAction;
use App\Http\Actions\Admin\ProductsImport\Pages\ShowImportedProductDetailsPageAction;
use App\Http\Actions\Admin\ProductsImport\Pages\ShowImportedProductsListPageAction;
use App\Http\Actions\Admin\ProductsImport\Pages\ShowProductImportPageAction;
use App\Http\Actions\Admin\ProductsImport\RemoveImportedProductImageAction;
use App\Http\Actions\Admin\ProductsImport\SaveImportedProductAction;
use App\Http\Actions\Admin\ProductsImport\UploadImportedProductImageAction;
use App\Http\Actions\Admin\ProductsImport\UploadProductsImportFileAction;
use App\Http\Actions\Admin\ProductTypes\Pages\ShowProductTypeCreatePageAction;
use App\Http\Actions\Admin\ProductTypes\Pages\ShowProductTypeEditPageAction;
use App\Http\Actions\Admin\ProductTypes\Pages\ShowProductTypesListPageAction;
use App\Http\Actions\Admin\ProductTypes\ProductTypeCreateAction;
use App\Http\Actions\Admin\ProductTypes\ProductTypeDeleteAction;
use App\Http\Actions\Admin\ProductTypes\ProductTypeEditAction;
use App\Http\Actions\Admin\ProductSubtypes\Pages\ShowProductSubtypesListPageAction;
use App\Http\Actions\Admin\ProductSubtypes\Pages\ShowProductSubtypeCreatePageAction;
use App\Http\Actions\Admin\ProductSubtypes\Pages\ShowProductSubtypeEditPageAction;
use App\Http\Actions\Admin\ProductSubtypes\ProductSubtypeCreateAction;
use App\Http\Actions\Admin\ProductSubtypes\ProductSubtypeEditAction;
use App\Http\Actions\Admin\ProductSubtypes\ProductSubtypeDeleteAction;
use App\Http\Actions\Admin\SEO\FilterGroupCreateAction;
use App\Http\Actions\Admin\SEO\FilterGroupDeleteAction;
use App\Http\Actions\Admin\SEO\FilterGroupEditAction;
use App\Http\Actions\Admin\SEO\Pages\ShowEditRobotsTxtEditPageAction;
use App\Http\Actions\Admin\SEO\Pages\ShowFilterGroupCreatePageAction;
use App\Http\Actions\Admin\SEO\Pages\ShowFilterGroupEditPageAction;
use App\Http\Actions\Admin\SEO\Pages\ShowFilterGroupListPageAction;
use App\Http\Actions\Admin\SEO\Pages\ShowSeoGenPageAction;
use App\Http\Actions\Admin\SEO\RobotsTxtEditAction;
use App\Http\Actions\Admin\SEO\SeogenEditAction;
use App\Http\Actions\Admin\StaticPages\Pages\StaticPageEditPageAction;
use App\Http\Actions\Admin\StaticPages\Pages\StaticPagesListPageAction;
use App\Http\Actions\Admin\StaticPages\StaticPageEditAction;
use App\Http\Actions\Admin\VisitRequests\Pages\ShowVisitRequestDetailsPageAction;
use App\Http\Actions\Admin\VisitRequests\Pages\ShowVisitRequestsListPageAction;
use App\Http\Actions\Admin\VisitRequests\VisitRequestEditAction;
use App\Http\Middleware\AdminsOnly;
use App\Http\Middleware\AuthenticatedOnly;
use App\Http\Middleware\SetLocaleAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware([
    AuthenticatedOnly::class,
    AdminsOnly::class,
    SetLocaleAdmin::class,
])->group(function () {
    Route::name('admin.dashboard.page')->get('dashboard', ShowDashboardPageAction::class);

    //Product fields
    Route::prefix('productField')->group(function () {
        //List
        Route::name('admin.product-field.list.page')->get('/',ShowProductFieldsListPageAction::class);

        //Create
        Route::name('admin.product-field.create.page')->get('create', ShowProductFieldCreatePageAction::class);
        Route::name('admin.product-field.create')->post('create', ProductFieldCreateAction::class);

        //Edit
        Route::name('admin.product-field.edit.page')->get('{productField}', ShowProductFieldEditPageAction::class);
        Route::name('admin.product-field.edit')->post('{productField}', ProductFieldEditAction::class);

        //Delete
        Route::name('admin.product-field.delete')->post('{productField}/delete', ProductFieldDeleteAction::class);
    });

    //Product attributes
    Route::prefix('productAttribute')->group(function () {
        //List
        Route::name('admin.product-attribute.list.page')->get('/',ShowProductAttributesListPageAction::class);

        //Create
        Route::name('admin.product-attribute.create.page')->get('create', ShowProductAttributeCreatePageAction::class);
        Route::name('admin.product-attribute.create')->post('create', ProductAttributeCreateAction::class);

        //Edit
        Route::name('admin.product-attribute.edit.page')->get('{productAttribute}', ShowProductAttributeEditPageAction::class);
        Route::name('admin.product-attribute.edit')->post('{productAttribute}', ProductAttributeEditAction::class);

        //Delete
        Route::name('admin.product-attribute.delete')->post('{productAttribute}/delete', ProductAttributeDeleteAction::class);
    });

    //Product types
    Route::prefix('productType')->group(function () {
        //List
        Route::name('admin.product-type.list.page')->get('/', ShowProductTypesListPageAction::class);

        //Create
        Route::name('admin.product-type.create.page')->get('create', ShowProductTypeCreatePageAction::class);
        Route::name('admin.product-type.create')->post('create', ProductTypeCreateAction::class);

        //Edit
        Route::name('admin.product-type.edit.page')->get('{productType}', ShowProductTypeEditPageAction::class);
        Route::name('admin.product-type.edit')->post('{productType}', ProductTypeEditAction::class);

        //Delete
        Route::name('admin.product-type.delete')->post('{productType}/delete', ProductTypeDeleteAction::class);
    });

    //Product subtypes (delete)
    /*Route::prefix('productSubtype')->group(function () {
        //List
        Route::name('admin.product-subtype.list.page')->get('/', ShowProductSubtypesListPageAction::class);

        //Create
        Route::name('admin.product-subtype.create.page')->get('create', ShowProductSubtypeCreatePageAction::class);
        Route::name('admin.product-subtype.create')->post('create', ProductSubtypeCreateAction::class);

        //Edit
        Route::name('admin.product-subtype.edit.page')->get('{productSubtype}', ShowProductSubtypeEditPageAction::class);
        Route::name('admin.product-subtype.edit')->post('{productSubtype}', ProductSubtypeEditAction::class);

        //Delete
        Route::name('admin.product-subtype.delete')->post('{productSubtype}/delete', ProductSubtypeDeleteAction::class);
    });*/

    //Product Categories
    Route::prefix('productCategory')->group(function () {
        //List
        Route::name('admin.product-category.list-by-product-type.page')->get('/', ShowCategoriesListByProductTypePageAction::class);
        Route::name('admin.product-category.list.page')->get('/{productType}', ShowCategoriesListPageAction::class);

        //Create
        Route::name('admin.product-category.create.page')->get('/{productType}/create', ShowCategoryCreatePageAction::class);
        Route::name('admin.product-category.create')->post('/{productType}/create', CategoryCreateAction::class);

        //Edit
        Route::name('admin.product-category.edit.page')->get('/{productType}/{productCategory}', ShowCategoryEditPageAction::class);
        Route::name('admin.product-category.edit')->post('/{productType}/{productCategory}', CategoryEditAction::class);

        //Delete
        Route::name('admin.product-category.delete')->post('/{productType}/{productCategory}/delete', CategoryDeleteAction::class);
    });

    //Currencies
    Route::prefix('currency')->group(function () {
        //List
        Route::name('admin.currency.list.page')->get('/', ShowCurrenciesListPageAction::class);

        //Create
        Route::name('admin.currency.create.page')->get('create', ShowCurrencyCreatePageAction::class);
        Route::name('admin.currency.create')->post('create', CurrencyCreateAction::class);

        //Edit
        Route::name('admin.currency.edit.page')->get('{currency}', ShowCurrencyEditPageAction::class);
        Route::name('admin.currency.edit')->post('{currency}', CurrencyEditAction::class);

        //Delete
        Route::name('admin.currency.delete')->post('{currency}/delete', CurrencyDeleteAction::class);
    });

    //Brands
    Route::prefix('brand')->group(function () {
        //List
        Route::name('admin.brand.list.page')->get('/', ShowBrandsListPageAction::class);
        Route::name('admin.brand.collections.list')->get('{brand}/collections', GetCollectionsByBrandAction::class);

        //Create
        Route::name('admin.brand.create.page')->get('create', ShowBrandCreatePageAction::class);
        Route::name('admin.brand.create')->post('create', BrandCreateAction::class);

        //Edit
        Route::name('admin.brand.edit.page')->get('{brand}', ShowBrandEditPageAction::class);
        Route::name('admin.brand.edit')->post('{brand}', BrandEditAction::class);

        //Delete
        Route::name('admin.brand.delete')->post('{brand}/delete', BrandDeleteAction::class);
    });

    //Collections
    Route::prefix('collection')->group(function () {
        //List
        Route::name('admin.collection.list.page')->get('/', ShowCollectionsListPageAction::class);
        Route::name('admin.collection.search')->get('/search', SearchCollectionAction::class);

        //Create
        Route::name('admin.collection.create.page')->get('create', ShowCollectionCreatePageAction::class);
        Route::name('admin.collection.create')->post('create', CollectionCreateAction::class);

        //Edit
        Route::name('admin.collection.edit.page')->get('{collection}', ShowCollectionEditPageAction::class);
        Route::name('admin.collection.edit')->post('{collection}', CollectionEditAction::class);

        //Delete
        Route::name('admin.collection.delete')->post('{collection}/delete', CollectionDeleteAction::class);
    });

    //Colors
    Route::prefix('color')->group(function () {
        //List
        Route::name('admin.color.list.page')->get('/', ShowColorsListPageAction::class);

        //Create
        Route::name('admin.color.create.page')->get('create', ShowColorCreatePageAction::class);
        Route::name('admin.color.create')->post('create', ColorCreateAction::class);

        //Edit
        Route::name('admin.color.edit.page')->get('{color}', ShowColorEditPageAction::class);
        Route::name('admin.color.edit')->post('{color}', ColorEditAction::class);

        //Delete
        Route::name('admin.color.delete')->post('{color}/delete', ColorDeleteAction::class);
    });

    //Countries
    Route::prefix('country')->group(function () {
        //List
        Route::name('admin.country.list.page')->get('/', ShowCountriesListPageAction::class);

        //Create
        Route::name('admin.country.create.page')->get('create', ShowCountryCreatePageAction::class);
        Route::name('admin.country.create')->post('create', CountryCreateAction::class);

        //Edit
        Route::name('admin.country.edit.page')->get('{country}', ShowCountryEditPageAction::class);
        Route::name('admin.country.edit')->post('{country}', CountryEditAction::class);

        //Delete
        Route::name('admin.country.delete')->post('{country}/delete', CountryDeleteAction::class);

    });

    //Products
    Route::prefix('product/')->group(function () {
        Route::name('admin.product.list.all')->get('/search', GetAllProductsBySearchAction::class);
        Route::name('admin.product.list.sub')->get('/searchSub', GetSubProductsBySearchAction::class);
    });
    Route::prefix('product/{productType}')->group(function () {
        //List
        Route::name('admin.product.list.page')->get('/', ShowProductsListPageAction::class);
        Route::name('admin.product.list')->get('/search', GetProductsBySearchAction::class);

        //Single
        Route::name('admin.product.parent')->get('{product}/data', GetParentProductDataAction::class);

        //Create
        Route::name('admin.product.create.page')->get('create', ShowProductCreatePageAction::class);
        Route::name('admin.product.create')->post('create', ProductCreateAction::class);

        //Edit
        Route::name('admin.product.edit.page')->get('{product}', ShowProductEditPageAction::class);
        Route::name('admin.product.edit')->post('{product}', ProductEditAction::class);

        //Delete
        Route::name('admin.product.delete')->post('{product}/delete', ProductDeleteAction::class);
    });

    //Orders
    Route::prefix('orders')->group(function () {
        //List
        Route::name('admin.order.list.page')->get('/', ShowOrdersListPageAction::class);

        //Single
        Route::name('admin.order.details.page')->get('/{order}', ShowOrderDetailsPageAction::class);
        Route::name('admin.order.edit')->post('/{order}', UpdateOrderAction::class);
    });

    //Products import
    Route::prefix('productsImport')->group(function () {
        Route::name('admin.products-import.page')->get('/{productType}', ShowProductImportPageAction::class);
        Route::name('admin.products-import.list')->get('/{productType}/list', ShowImportedProductsListPageAction::class);
        Route::name('admin.products-import.download-example')->get('/{productType}/example', DownloadProductsImportExampleAction::class);
        Route::name('admin.products-import.upload')->post('/{productType}/upload', UploadProductsImportFileAction::class);
        Route::name('admin.products-import.delete-products')->post('{productType}/deleteProducts', DeleteImportedProductsAction::class);
        Route::name('admin.products-import.save-products')->post('{productType}/save', SaveImportedProductAction::class);

        Route::name('admin.products-import.upload-image')->post('/{importedProduct}/uploadImage', UploadImportedProductImageAction::class);
        Route::name('admin.products-import.remove-image')->post('/{importedProduct}/removeImage', RemoveImportedProductImageAction::class);
        Route::name('admin.products-import.details')->get('/{importedProduct}/details', ShowImportedProductDetailsPageAction::class);
        Route::name('admin.products-import.delete')->post('/{importedProduct}/delete', DeleteProductFromListAction::class);
    });

    //Static pages
    Route::prefix('staticPages')->group(function () {
        Route::name('admin.static-pages.list.page')->get('/', StaticPagesListPageAction::class);
        Route::name('admin.static-pages.edit.page')->get('/{staticPage}', StaticPageEditPageAction::class);
        Route::name('admin.static-pages.edit')->post('/{staticPage}', StaticPageEditAction::class);
    });

    //Blog category
    Route::prefix('blogCategory')->group(function () {
        //List
        Route::name('admin.blog-category.list.page')->get('/', ShowBlogCategoriesListPageAction::class);

        //Create
        Route::name('admin.blog-category.create.page')->get('create', ShowBlogCategoryCreatePageAction::class);
        Route::name('admin.blog-category.create')->post('create', BlogCategoryCreateAction::class);

        //Edit
        Route::name('admin.blog-category.edit.page')->get('{blogCategory}', ShowBlogCategoryEditPageAction::class);
        Route::name('admin.blog-category.edit')->post('{blogCategory}', BlogCategoryEditAction::class);

        //Delete
        Route::name('admin.blog-category.delete')->post('{blogCategory}/delete', BlogCategoryDeleteAction::class);
    });

    //Blog article
    Route::prefix('blogArticle')->group(function () {
        //List
        Route::name('admin.blog-article.list.page')->get('/', ShowBlogArticlesListPageAction::class);

        //Create
        Route::name('admin.blog-article.create.page')->get('create', ShowBlogArticleCreatePageAction::class);
        Route::name('admin.blog-article.create')->post('create', BlogArticleCreateAction::class);

        //Edit
        Route::name('admin.blog-article.edit.page')->get('{blogArticle}', ShowBlogArticleEditPageAction::class);
        Route::name('admin.blog-article.edit')->post('{blogArticle}', BlogArticleEditAction::class);

        //Delete
        Route::name('admin.blog-article.delete')->post('{blogArticle}/delete', BlogArticleDeleteAction::class);
    });

    //Blog slides
    Route::prefix('blogSlide')->group(function () {
        Route::name('admin.blog-slide.edit.page')->get('edit', ShowBlogSlidesEditPageAction::class);
        Route::name('admin.blog-slide.edit')->post('edit', BlogSlidesEditAction::class);
    });

    //Home page
    Route::prefix('homePage')->group(function () {
        Route::name('admin.home-page.edit.page')->get('edit', ShowHomePageEditPageAction::class);
        Route::name('admin.home-page.edit')->post('edit', HomePageEditAction::class);
    });

    //Visit requests
    Route::prefix('visitRequest')->group(function () {
        Route::name('admin.visit-request.list.page')->get('/', ShowVisitRequestsListPageAction::class);
        Route::name('admin.visit-request.details.page')->get('/{visitRequest}', ShowVisitRequestDetailsPageAction::class);
        Route::name('admin.visit-request.edit')->post('/{visitRequest}', VisitRequestEditAction::class);
    });

    //Seo
    Route::prefix('seo')->group(function () {
        Route::prefix('robots')->group(function () {
            Route::name('admin.seo.robots-txt.edit.page')->get('/edit', ShowEditRobotsTxtEditPageAction::class);
            Route::name('admin.seo.robots-txt.edit')->post('/edit', RobotsTxtEditAction::class);
        });

        Route::prefix('seoGen')->group(function () {
            Route::name('admin.seo-gen.edit.page')->get('/edit', ShowSeoGenPageAction::class);
            Route::name('admin.seo-gen.edit')->post('/edit', SeogenEditAction::class);
        });

        Route::prefix('filterGroups')->group(function () {
            Route::name('admin.filter-groups.list.page')->get('/', ShowFilterGroupListPageAction::class);

            Route::name('admin.filter-groups.create.page')->get('/create', ShowFilterGroupCreatePageAction::class);
            Route::name('admin.filter-groups.create')->post('/create', FilterGroupCreateAction::class);

            Route::name('admin.filter-groups.edit.page')->get('/{filterGroup}', ShowFilterGroupEditPageAction::class);
            Route::name('admin.filter-groups.edit')->post('/{filterGroup}', FilterGroupEditAction::class);

            Route::name('admin.filter-groups.delete')->post('/{filterGroup}/delete', FilterGroupDeleteAction::class);
        });
    });
});
