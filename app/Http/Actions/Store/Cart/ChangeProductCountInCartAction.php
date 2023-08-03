<?php

namespace App\Http\Actions\Store\Cart;

use App\Models\Product;
use App\Services\Cart\CartService;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;
use App\Http\Resources\Store\Cart\CartResource;
use App\Http\Requests\Store\Cart\ChangeProductCountInCartRequest;

class ChangeProductCountInCartAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Product $product,
        ChangeProductCountInCartRequest $request,
        CartService $cartService,
        WishListService $wishListService,
    )
    {
        $cart = $this->getCart($cartService);
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        $cartService->changeProductCount($cart, $product, $request->toDTO());

        return CartResource::make($cartService->getProductsInCartWithSummary($cart, $wishList));
    }
}
