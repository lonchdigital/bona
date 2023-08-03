<?php

namespace App\Http\Actions\Store\Cart;

use App\Models\Product;
use App\Services\Cart\CartService;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;
use App\Http\Resources\Store\Cart\CartResource;

class DeleteProductFromCartAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Product $product,
        CartService $cartService,
        WishListService $wishListService,
    )
    {
        $cart = $this->getCart($cartService);
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        $cartService->deleteProductFromCart($cart, $product);

        return CartResource::make($cartService->getProductsInCartWithSummary($cart, $wishList));
    }
}
