<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Cart\GetProductsSummaryWithDeliveryRequest;
use App\Http\Resources\Store\Cart\CartSummaryWithDelivery;
use App\Services\Cart\CartService;
use App\Services\WishList\WishListService;

class GetProductsSummaryWithDelivery extends BaseAction
{
    use NeedCart;

    public function __invoke(
        GetProductsSummaryWithDeliveryRequest $request,
        CartService $cartService,
        WishListService $wishListService,
    )
    {
        $cart = $this->getCart($cartService);
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        return CartSummaryWithDelivery::make($cartService->getCartSummaryWithDelivery($request->toDTO(), $cart, $wishList));
    }
}
