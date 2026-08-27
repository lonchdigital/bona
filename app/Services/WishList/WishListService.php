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
    public function getWishListByUser(?User $user): ?WishList
    {
        if (!$user) {
            return null;
        }

        return $user->wishList;
    }

    public function getWishListByToken(string $token): ?WishList
    {
        return WishList::where('token', $token)->first();
    }

    public function getCurrentWishList(?User $user, ?string $guestToken): ?WishList
    {
        return $this->getWishListByUser($user) ?? ($guestToken ? $this->getWishListByToken($guestToken) : null);
    }

    public function createWishListForUser(User $user): WishList
    {
        return WishList::create([
            'owner_id' => $user->id,
            'access_token' => $this->generateAccessToken(),
        ]);
    }

    public function createWishListForGuest(string $token): WishList
    {
        return WishList::create([
            'owner_id' => null,
            'token' => $token,
            'access_token' => $this->generateAccessToken(),
        ]);
    }

    /**
     * Randomness alone. Appending the id of the newest row cost a query and
     * gave two lists created at the same moment the same suffix, leaving
     * uniqueness to the random part regardless.
     */
    private function generateAccessToken(): string
    {
        return Str::random(40);
    }

    public function getProductsByWishList(?WishList $wishList): Collection
    {
        if ($wishList) {
            return $wishList->products;
        }
        return collect();
    }

    public function addProductToWishList(WishList $wishList, Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($wishList, $product) {
            if (!$wishList->products()->where('product_id', $product->id)->exists()) {
                $wishList->products()->attach($product->id);
            }

            // attach() leaves the list's own timestamp alone, and the thirty
            // day window for a guest is measured from it.
            $wishList->touch();

            return ServiceActionResult::make(true, trans('base.wish_list_product_add_success'));
        });
    }

    public function removeProductFromWishList(WishList $wishList, Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($wishList, $product) {
            if ($wishList->products()->where('product_id', $product->id)->exists()) {
                $wishList->products()->detach($product->id);
            }

            $wishList->touch();

            return ServiceActionResult::make(true, trans('base.wish_list_product_remove_success'));
        });
    }

    public function mergeGuestWishListIntoUserWishList(User $user, string $guestToken): void
    {
        $guestWishList = $this->getWishListByToken($guestToken);

        if (!$guestWishList) {
            return;
        }

        $this->coverWithDBTransaction(function () use ($user, $guestWishList) {
            $userWishList = $this->getWishListByUser($user);

            if (!$userWishList) {
                $guestWishList->owner_id = $user->id;
                $guestWishList->token = null;
                $guestWishList->save();

                return;
            }

            $productIds = $guestWishList->products()->select(['product_id'])->get()->pluck('product_id');

            if ($productIds->isNotEmpty()) {
                $userWishList->products()->syncWithoutDetaching($productIds);
            }

            $guestWishList->delete();
        });
    }

    public function getWishListProductsId(?WishList $wishList): Collection
    {
        if ($wishList) {
            return $wishList->products()->select(['product_id'])->get()->pluck('product_id');
        }

        return collect();
    }

    public function getWishListProductsSlugs(?WishList $wishList): Collection
    {
        if ($wishList) {
            return $wishList->products()->select(['products.slug'])->get()->pluck('slug');
        }

        return collect();
    }
}
