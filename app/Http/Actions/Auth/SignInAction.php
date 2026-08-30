<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\SignInRequest;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use App\Services\Cart\GuestCartToken;
use App\Services\WishList\GuestWishListToken;
use App\Services\WishList\WishListService;

class SignInAction
{
    public function __invoke(
        SignInRequest $request,
        AuthService $service,
        CartService $cartService,
        GuestCartToken $guestCartToken,
        WishListService $wishListService,
        GuestWishListToken $guestWishListToken,
    ) {
        $dto = $request->toDTO();

        $signInResult = $service->signIn($dto);

        if ($signInResult) {
            if ($token = $guestCartToken->existing()) {
                $cartService->mergeGuestCartIntoUserCart(auth()->user(), $token);
                $guestCartToken->forget();
            }

            if ($token = $guestWishListToken->existing()) {
                $wishListService->mergeGuestWishListIntoUserWishList(auth()->user(), $token);
                $guestWishListToken->forget();
            }

            if (auth()->user()->isAdmin()) {
                return redirect()->route('admin.order.list.page');
            } else {
                return redirect()->route('user.profile.orders.page');
            }
        } else {
            return back()->withErrors([
                'password' => trans('auth.credentials_are_incorrect'),
            ])->withInput([
                'email' => $dto->email,
                'remember_me' => $dto->rememberMe,
            ]);
        }

    }
}
