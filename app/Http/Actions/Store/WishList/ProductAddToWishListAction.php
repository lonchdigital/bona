<?php

namespace App\Http\Actions\Store\WishList;

use App\Models\Product;
use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;
use Illuminate\Http\Request;

class ProductAddToWishListAction extends BaseAction
{
    public function __invoke(Product $product, Request $request, WishListService $wishListService)
    {
        $user = $this->getAuthUser();

        $wishList = $wishListService->getWishListByUser($user);

        $result = $wishListService->addProductToWishList(
            $user,
            $wishList,
            $product
        );

        return $this->handleActionResult('', $request, $result);
    }
}
