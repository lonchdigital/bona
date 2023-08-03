<?php

namespace App\Http\Actions\Store\WishList;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;

class ProductRemoveFromWishListAction extends BaseAction
{
    public function __invoke(Product $product, Request $request, WishListService $wishListService)
    {
        $user = $this->getAuthUser();

        $wishList = $wishListService->getWishListByUser($user);

        $result = $wishListService->removeProductFromWishList(
            $user,
            $wishList,
            $product
        );

        return $this->handleActionResult('', $request, $result);
    }
}
