<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Resources\BaseActionResource;
use App\Services\Cart\CartService;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;
use App\Http\Resources\Store\Cart\CartResource;
use App\Http\Requests\Store\Cart\AddPromoCodeToCartRequest;

class AddPromoCodeToCartAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        AddPromoCodeToCartRequest $request,
        CartService $cartService,
        WishListService $wishListService,
    )
    {
        $cart = $this->getCart($cartService);
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        $result = $cartService->attachPromoCode($request->toDTO(), $cart);

        if ($result->isSuccess()) {
            return CartResource::make($cartService->getProductsInCartWithSummary($cart, $wishList));
        } else {
            return response(BaseActionResource::make([
                    'success' => $result->isSuccess(),
                    'message' => $result->getMessage(),
                    'redirect_to' => '',
                ]))->setStatusCode(422);
        }


    }
}
