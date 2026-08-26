<?php

namespace App\Http\Actions\Store\WishList;

use App\Http\Actions\Admin\BaseAction;
use App\Services\WishList\WishListService;

class GetWishListProductSlugsAction extends BaseAction
{
    use NeedWishList;

    public function __invoke(WishListService $wishListService)
    {
        $wishList = $this->getWishList($wishListService);

        $slugs = $wishListService->getWishListProductsSlugs($wishList);

        return response()->json([
            'data' => [
                'slugs' => $slugs,
                // Counted from what was already fetched rather than asking the
                // database a second time.
                'count' => $slugs->count(),
            ],
        ]);
    }
}
