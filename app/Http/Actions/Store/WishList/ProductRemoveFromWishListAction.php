<?php

namespace App\Http\Actions\Store\WishList;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Base\ServiceActionResult;
use App\Services\WishList\WishListService;

class ProductRemoveFromWishListAction extends BaseAction
{
    use NeedWishList;

    public function __invoke(Product $product, Request $request, WishListService $wishListService)
    {
        $wishList = $this->getWishList($wishListService);

        // Nothing to remove from is the state the caller wanted anyway.
        if (!$wishList) {
            return $this->handleActionResult(
                route('store.wishlist.private.page'),
                $request,
                ServiceActionResult::make(true, trans('base.wish_list_product_remove_success'))
            );
        }

        $result = $wishListService->removeProductFromWishList(
            $wishList,
            $product
        );

        return $this->handleActionResult(route('store.wishlist.private.page'), $request, $result);
    }
}
