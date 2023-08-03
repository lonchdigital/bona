<?php

namespace App\Http\Actions\Store\Cart;

use App\Models\Cart;
use App\Services\Cart\CartService;

trait NeedCart
{
    public function getCart(CartService $cartService): Cart
    {
        if ($this->getAuthUser()) {
            $cart = $cartService->getCartForAuthUser($this->getAuthUser());
        } else {
            $cart = $cartService->getCartForGuestUser(session_id());
        }

        if (!$cart) {
            $cart = $this->getAuthUser() ? $cartService->createCartByUser($this->getAuthUser()) : $cartService->createCartByToken(session_id());
        }

        return $cart;
    }
}
