<?php

namespace App\Http\Actions\Store\WishList\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\WishList;
use App\Services\Currency\CurrencyService;
use App\Services\WishList\WishListService;

class ShowWishListByTokenPageAction extends BaseAction
{
    public function __invoke(
        WishList $wishList,
        WishListService $wishListService,
        CurrencyService $currencyService,
    )
    {
        return view('pages.store.wish-list', [
            'isPublic' => true,
            'wishList' => $wishList,
            'products' => $wishListService->getProductsByWishList($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
