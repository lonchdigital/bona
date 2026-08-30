<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Store\Cart\CartResource;
use App\Models\Cart;
use App\Services\Cart\CartService;
use App\Services\WishList\WishListService;

class GetProductsInCartWithSummaryAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CartService $cartService,
        WishListService $wishListService,
    ) {
        // Reading a summary must not bring a cart into being.
        $cart = $this->getExistingCart($cartService) ?? new Cart;
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        return CartResource::make($cartService->getProductsInCartWithSummary($cart, $wishList));
    }
}
