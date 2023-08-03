<?php

namespace App\Http\Actions\Store\Calculator\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Product;
use App\Services\WishList\WishListService;

class ShowCalculatorPageAction extends BaseAction
{
    public function __invoke(
        ?Product $product = null,
        WishListService $wishListService
    )
    {
        if (!$product->id) {
            $product = null;
        }

        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }

        return view('pages.store.calculator', [
            'wishListProducts' => $wishListService->getProductsByWishList($wishList),
            'preselectedProduct' => $product,
        ]);
    }
}
