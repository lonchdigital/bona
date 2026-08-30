<?php

namespace App\Http\Actions\Store\Product\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;
use App\Services\ProductReview\ProductReviewService;
use App\Services\WishList\WishListService;
use App\Support\LastModified;

class ShowProductPageAction extends BaseAction
{
    public function __invoke(
        Product $product,
        CurrencyService $currencyService,
        ProductService $productService,
        WishListService $wishListService,
        CartService $cartService,
        ProductReviewService $productReviewService,
    ) {

        $product->load([
            //            'color',
            //            'children.color',
            'productType',
            'productType.fields',
            'productType.fields.options',
        ]);

        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }

        $cart = $this->getAuthUser() ? $cartService->getCartForAuthUser($this->getAuthUser()) : $cartService->getCartForGuestUser(request()->session()->getId());
        if (is_null($cart)) {
            $cart = $cartService->createCartByToken(request()->session()->getId());
        }

        $isProductInCart = false;
        if ($cart) {
            $isProductInCart = $cartService->isProductInCart($product, $cart);
        }

        $sub_products = (! is_null($product->sub_products)) ? json_decode($product->sub_products) : false;

        $product->meta_title = ($product->meta_title) ?
            $productService->replaceTagsWithData($product->meta_title, $product) :
            $productService->replaceTagsWithData($product->productType->meta_product_title, $product);

        $product->meta_description = ($product->meta_description) ?
            $productService->replaceTagsWithData($product->meta_description, $product) :
            $productService->replaceTagsWithData($product->productType->meta_product_description, $product);

        $template = 'pages.store.product';
        if ($product->productType->id == config('constants.ROZSUVNI_DVERI_ID')) {
            $template = 'pages.store.product-variety.rozsuvni-dveri-product';
        }

        $product->meta_tags = $this->handleFollowTag($product->meta_tags);
        LastModified::set($product->updated_at);

        return view($template, [
            'product' => $product,
            //            'categoryProducts' => $categoryProducts,
            'categoryProducts' => $productService->getSelectedSubItemsWithCategories($sub_products),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'sameTypeProducts' => $productService->getSameTypeProducts($product),
            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'isProductInCart' => $isProductInCart,
            'productText' => $productService->getProductTextByLanguage($product->id, app()->getLocale()),
            'characteristics' => $productService->getProductCharacteristics($product->id),
            'productGallery' => $productService->getProductGallery($product->id),
            'productVideos' => $productService->getProductVideos($product->id),

            'cart' => $cart,
            'cartService' => $cartService,

            'attributeOptions' => $productService->getAttributeNamesWithOptions($product->id, $product->productType),

            // Only approved reviews reach the page, and the rating summary is
            // null until at least one exists.
            'productReviews' => $productReviewService->getApprovedReviews($product),
            'productRatingSummary' => $productReviewService->getRatingSummary($product),
        ]);
    }
}
