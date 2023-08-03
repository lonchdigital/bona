<?php

namespace App\Http\Actions\Store\WishList\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\WishList\WishListService;

class ShowWishListPageAction extends BaseAction
{
    use NeedCart;
    public function __invoke(
        WishListService $wishListService,
        CurrencyService $currencyService,
        CartService $cartService,
    )
    {
        $wishList = $wishListService->getWishListByUser($this->getAuthUser());

        $cart = $this->getCart($cartService);

        return view('pages.store.wish-list', [
            'isPublic' => false,
            'wishList' => $wishList,
            'cart' => $cart,
            'products' => $wishListService->getProductsByWishList($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
