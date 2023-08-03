<?php

namespace App\Services\WishList;

use App\Models\User;
use App\Models\Product;
use App\Models\WishList;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WishListService extends BaseService
{
    public function getWishListByUser(User $user): ?WishList
    {
        return $user->wishList;
    }

    public function getProductsByWishList(?WishList $wishList): Collection
    {
        if ($wishList) {
            return $wishList->products;
        }
        return collect();
    }

    public function addProductToWishList(User $owner, ?WishList $wishList, Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($owner, $wishList, $product) {
            if (!$wishList) {
                $latestWishList = WishList::latest()->first();

                $wishList = WishList::create([
                    'owner_id' => $owner->id,
                    'access_token' => Str::random(16) . '_' . ($latestWishList ? $latestWishList->id : 1),
                ]);
            }

            if (!$wishList->products()->where('product_id', $product->id)->exists()) {
                $wishList->products()->attach($product->id);
            }

            return ServiceActionResult::make(true, trans('base.wish_list_product_add_success'));
        });
    }

    public function removeProductFromWishList(User $owner, ?WishList $wishList, Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($owner, $wishList, $product) {

            if ($wishList && $wishList->products()->where('product_id', $product->id)->exists()) {
                $wishList->products()->detach($product->id);
            }

            return ServiceActionResult::make(true, trans('base.wish_list_product_remove_success'));
        });
    }

    public function getWishListProductsId(?WishList $wishList): Collection
    {
        if ($wishList) {
            return $wishList->products()->select(['product_id'])->get()->pluck('product_id');
        }

        return collect();
    }
}
