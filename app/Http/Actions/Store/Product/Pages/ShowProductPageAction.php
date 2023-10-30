<?php

namespace App\Http\Actions\Store\Product\Pages;

use App\Models\Product;
use App\Http\Actions\Admin\BaseAction;
use App\Models\SeoGenConfig;
use App\Services\Cart\CartService;
use App\Services\Product\ProductService;
use App\Services\Currency\CurrencyService;
use App\Services\Seogen\SeogenService;
use App\Services\WishList\WishListService;

class ShowProductPageAction extends BaseAction
{
    public function __invoke(
        Product $product,
        CurrencyService $currencyService,
        ProductService $productService,
        WishListService $wishListService,
        CartService $cartService,
        SeogenService $seogenService,
    )
    {

//        dd($product);
        $product->load([
            'color',
            'children.color',
            'productType',
            'productType.fields',
            'productType.fields.options',
        ]);

        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }

        $cart = $this->getAuthUser() ? $cartService->getCartForAuthUser($this->getAuthUser()) : $cartService->getCartForGuestUser(session_id());

        $isProductInCart = false;
        if ($cart) {
            $isProductInCart = $cartService->isProductInCart($product, $cart);
        }

        return view('pages.store.product', [
            'product' => $product,
//            'categoryProducts' => $categoryProducts,
            'categoryProducts' => $productService->getSelectedSubItemsWithCategories(json_decode($product->sub_products)),
            'baseCurrency' => $currencyService->getBaseCurrency(),
//            'productsInSameCollection' => $productService->getProductsBySameCollection($product),
            'sameTypeProducts' => $productService->getSameTypeProducts($product),
            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'isProductInCart' => $isProductInCart,
            'seogenData' => $seogenService->getTagsForProducts($product->productType, $product),
            'productText' => $productService->getProductTextByLanguage($product->id, app()->getLocale()),
            'characteristics' => $productService->getProductCharacteristics($product->id),
            'productGallery' => $productService->getProductGallery($product->id),
            'productVideos' => $productService->getProductVideos($product->id),

            'cart' => $cart,
            'cartService' => $cartService,
        ]);
    }
}
