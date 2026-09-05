<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Store\Cart\CartResource;
use App\Models\Cart;
use App\Services\Cart\CartService;
use App\Services\WishList\WishListService;

class RemovePromoCodeFromCartAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CartService $cartService,
        WishListService $wishListService,
    ) {
        $cart = $this->getExistingCart($cartService) ?? new Cart;
        $wishList = $this->getAuthUser()
            ? $wishListService->getWishListByUser($this->getAuthUser())
            : null;

        if ($cart->exists) {
            $cartService->detachPromoCode($cart);
        }

        return CartResource::make($cartService->getProductsInCartWithSummary($cart->exists ? $cart->fresh() : $cart, $wishList));
    }
}
