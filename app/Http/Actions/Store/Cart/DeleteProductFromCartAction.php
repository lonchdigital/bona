<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Cart\DeleteProductFromCartRequest;
use App\Http\Resources\Store\Cart\CartResource;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\WishList\WishListService;

class DeleteProductFromCartAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Product $product,
        DeleteProductFromCartRequest $request,
        CartService $cartService,
        WishListService $wishListService,
    ) {
        $cart = $this->getCart($cartService);
        $wishList = $this->getAuthUser() ? $wishListService->getWishListByUser($this->getAuthUser()) : null;

        $cartService->deleteProductFromCart($cart, $product, $request->toDTO());

        return CartResource::make($cartService->getProductsInCartWithSummary($cart, $wishList));
    }
}
