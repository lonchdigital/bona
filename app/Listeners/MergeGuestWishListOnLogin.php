<?php

namespace App\Listeners;

use App\Services\WishList\GuestWishListToken;
use App\Services\WishList\WishListService;
use Illuminate\Auth\Events\Login;

/**
 * Hands a guest's list over to the account they just signed in to.
 *
 * The guest is found by their own cookie. Reading the session id here would
 * not work: Laravel regenerates it inside login(), before this event is
 * fired, so the list saved under the old id would never be found and the
 * merge would quietly do nothing.
 *
 * Deliberately not queued — the cookie belongs to this request.
 */
class MergeGuestWishListOnLogin
{
    public function __construct(
        private readonly WishListService $wishListService,
        private readonly GuestWishListToken $guestWishListToken,
    ) { }

    public function handle(Login $event): void
    {
        $guestToken = $this->guestWishListToken->existing();

        if (!$guestToken) {
            return;
        }

        $this->wishListService->mergeGuestWishListIntoUserWishList($event->user, $guestToken);

        $this->guestWishListToken->forget();
    }
}
