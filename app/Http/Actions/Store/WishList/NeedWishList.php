<?php

namespace App\Http\Actions\Store\WishList;

use App\Models\WishList;
use App\Services\WishList\GuestWishListToken;
use App\Services\WishList\WishListService;

trait NeedWishList
{
    /**
     * The visitor's list, if they have one. A guest is recognised by their own
     * cookie rather than the session id, which is regenerated on sign in and
     * would lose the list.
     */
    public function getWishList(WishListService $wishListService): ?WishList
    {
        return $wishListService->getCurrentWishList(
            $this->getAuthUser(),
            $this->guestWishListToken()->existing()
        );
    }

    /**
     * Only issues a guest token once there is something to keep, so a passing
     * reader is not given a cookie for nothing.
     */
    public function getOrCreateWishList(WishListService $wishListService): WishList
    {
        $wishList = $this->getWishList($wishListService);

        if ($wishList) {
            // Touching the list is what keeps a guest's cookie alive.
            $this->guestWishListToken()->refresh();

            return $wishList;
        }

        $user = $this->getAuthUser();

        return $user
            ? $wishListService->createWishListForUser($user)
            : $wishListService->createWishListForGuest($this->guestWishListToken()->ensure());
    }

    private function guestWishListToken(): GuestWishListToken
    {
        return app(GuestWishListToken::class);
    }
}
