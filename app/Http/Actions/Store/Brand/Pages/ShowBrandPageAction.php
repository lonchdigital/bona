<?php

namespace App\Http\Actions\Store\Brand\Pages;

use App\Models\Brand;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Brand\BrandService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;
use App\Services\Seogen\SeogenService;
use App\Services\WishList\WishListService;
use Abordage\LastModified\Facades\LastModified;

class ShowBrandPageAction extends BaseAction
{
    public function __invoke(
        Brand $brand,
        BrandService $brandService,
        ProductService $productService,
        WishListService $wishListService,
        CurrencyService $currencyService,
        SeogenService $seogenService,
    )
    {
        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }


        LastModified::set($brand->updated_at);

        return view('pages.store.brand', [
            'brand' => $brand,
            'bestsellers' => $productService->getBestSellersByBrandId($brand->id),
            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'discoverBrands' => $brandService->getDiscoverBrands($brand),
            'seogenData' => $seogenService->getTagsForBrands($brand),
        ]);
    }
}
