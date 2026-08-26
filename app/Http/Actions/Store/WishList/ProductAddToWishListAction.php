<?php

namespace App\Http\Actions\Store\WishList;

use App\Models\Product;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;
use Illuminate\Http\Request;

class ProductAddToWishListAction extends BaseAction
{
    use NeedWishList;

    public function __invoke(Product $product, Request $request, WishListService $wishListService)
    {
        $wishList = $this->getOrCreateWishList($wishListService);

        $result = $wishListService->addProductToWishList(
            $wishList,
            $product
        );

        return $this->handleActionResult(route('store.wishlist.private.page'), $request, $result);
    }
}
