<?php

namespace App\Services\WishList;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Identifies the wish list of a visitor who is not signed in.
 *
 * A cookie of its own rather than the session id: the session id is
 * regenerated on sign in, on sign out and whenever the session is refreshed,
 * so a guest would lose the list without touching anything, and the merge on
 * login would look for a list under an id that no longer exists. Storing a
 * session id in a table also leaves it in dumps and backups, where it is a
 * session hijacking vector.
 */
class GuestWishListToken
{
    public const COOKIE_NAME = 'wish_list_token';

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
     * The visitor's token, issuing one if they have none yet.
     *
     * An existing token is re-queued rather than left alone, so the thirty
     * days run from the last time the visitor changed their list instead of
     * from the first thing they ever saved.
     */
    public function ensure(): string
    {
        $token = $this->existing() ?? Str::random(40);

        $this->created = $token;

        Cookie::queue(
            self::COOKIE_NAME,
            $token,
            self::LIFETIME_MINUTES
        );

        return $token;
    }

    /**
     * Pushes the expiry back out to the full window without minting anything.
     *
     * ensure() only runs when a list is being created, so on its own it would
     * date the window from the first thing a visitor ever saved. This is what
     * makes the thirty days run from their last change instead.
     */
    public function refresh(): void
    {
        $token = $this->existing();

        if ($token === null) {
            return;
        }

        Cookie::queue(self::COOKIE_NAME, $token, self::LIFETIME_MINUTES);
    }

    /**
     * Dropped once the list has been handed over to a signed in account.
     */
    public function forget(): void
    {
        $this->created = null;

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
