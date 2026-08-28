<?php

namespace App\Http\Actions\Store\Cart;

use App\Models\Cart;
use App\Services\Cart\CartService;
use App\Services\Cart\GuestCartToken;

trait NeedCart
{
    public function getCart(CartService $cartService): Cart
    {
        if ($this->getAuthUser()) {
            $cart = $cartService->getCartForAuthUser($this->getAuthUser());
        } else {
            $cart = $cartService->getCartForGuestUser($this->guestCartToken()->existing() ?? '');
        }

        if (!$cart) {
            $cart = $this->getAuthUser()
                ? $cartService->createCartByUser($this->getAuthUser())
                : $cartService->createCartByToken($this->guestCartToken()->ensure());
        }

        return $cart;
    }

    /**
     * The visitor's cart if they have one, without making them a new one.
     *
     * The header widget asks for the cart summary on every page, and that used
     * to go through getCart(), which creates. Every visitor to any page was
     * therefore given a row of their own — over a hundred and eighty thousand
     * of them by now, six with anything in.
     */
    public function getExistingCart(CartService $cartService): ?Cart
    {
        if ($this->getAuthUser()) {
            return $cartService->getCartForAuthUser($this->getAuthUser());
        }

        $token = $this->guestCartToken()->existing();

        return $token ? $cartService->getCartForGuestUser($token) : null;
    }

    private function guestCartToken(): GuestCartToken
    {
        return app(GuestCartToken::class);
    }
}
