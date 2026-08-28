<?php

namespace App\Listeners;

use App\Services\Cart\CartService;
use App\Services\Cart\GuestCartToken;
use Illuminate\Auth\Events\Login;

/**
 * Hands a guest's cart over to the account they just signed in to.
 *
 * Without this the cart empties itself at exactly the wrong moment: someone
 * fills it, signs in to pay, and the shop looks for a cart belonging to their
 * account, finds none, and gives them a new empty one.
 *
 * Deliberately not queued — the cookie belongs to this request.
 */
class MergeGuestCartOnLogin
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly GuestCartToken $guestCartToken,
    ) { }

    public function handle(Login $event): void
    {
        $guestToken = $this->guestCartToken->existing();

        if (!$guestToken) {
            return;
        }

        $this->cartService->mergeGuestCartIntoUserCart($event->user, $guestToken);

        $this->guestCartToken->forget();
    }
}
