<?php

namespace App\Services\Cart;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Identifies the cart of a visitor who is not signed in.
 *
 * A cookie of its own rather than the session id. Laravel issues a new session
 * id on sign in, on sign out and whenever the session is refreshed, so a cart
 * keyed on it is looked for under an id that no longer exists — the visitor
 * fills a cart, signs in to pay, and finds it empty.
 *
 * The same reasoning as GuestWishListToken, which this mirrors.
 */
class GuestCartToken
{
    public const COOKIE_NAME = 'cart_token';

    public const LIFETIME_DAYS = 30;

    private const LIFETIME_MINUTES = 60 * 24 * self::LIFETIME_DAYS;

    /**
     * Remembers a token created during this request: a queued cookie is not
     * readable from the request it was set in.
     */
    private ?string $created = null;

    /**
     * The token this visitor already carries, without handing out a new one.
     */
    public function existing(): ?string
    {
        if ($this->created !== null) {
            return $this->created;
        }

        $token = request()->cookie(self::COOKIE_NAME);

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * The visitor's token, issuing one if they have none yet, and pushing the
     * expiry back out so the window runs from their last visit.
     */
    public function ensure(): string
    {
        $token = $this->existing() ?? Str::random(40);

        $this->created = $token;

        Cookie::queue(self::COOKIE_NAME, $token, self::LIFETIME_MINUTES);

        return $token;
    }

    /**
     * Dropped once the cart has been handed over to a signed in account.
     */
    public function forget(): void
    {
        $this->created = null;

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
