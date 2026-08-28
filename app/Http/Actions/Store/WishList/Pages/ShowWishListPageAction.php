<?php

namespace App\Http\Actions\Store\WishList\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Http\Actions\Store\WishList\NeedWishList;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\WishList\WishListService;

class ShowWishListPageAction extends BaseAction
{
    use NeedCart;
    use NeedWishList;

    public function __invoke(
        WishListService $wishListService,
        CurrencyService $currencyService,
        CartService $cartService,
    )
    {
        $wishList = $this->getWishList($wishListService);


        return view('pages.store.wish-list', [
            'isPublic' => false,
            'wishList' => $wishList,
            'products' => $wishListService->getProductsByWishList($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
