<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\SignInRequest;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use App\Services\Cart\GuestCartToken;
use App\Services\WishList\GuestWishListToken;
use App\Services\WishList\WishListService;
use Illuminate\Support\Arr;

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
                $redirectTo = route('admin.order.list.page');
            } else {
                $redirectTo = $request->input('redirect_to');
                if (! is_string($redirectTo) || ! str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
                    $redirectTo = route('user.profile.orders.page');
                }
            }

            if ($request->expectsJson()) {
                $this->preserveCheckoutDraft($request);

                return response()->json([
                    'redirect_to' => $redirectTo,
                ]);
            }

            return redirect()->to($redirectTo);
        }

        $message = trans('auth.credentials_are_incorrect');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [
                    'password' => [$message],
                ],
            ], 422);
        }

        return back()->withErrors([
            'password' => $message,
        ])->withInput([
            'email' => $dto->email,
            'remember_me' => $dto->rememberMe,
            'redirect_to' => $request->input('redirect_to'),
        ]);
    }

    private function preserveCheckoutDraft(SignInRequest $request): void
    {
        $draft = json_decode((string) $request->input('checkout_draft'), true);
        if (! is_array($draft)) {
            return;
        }

        $draft = Arr::only($draft, [
            'delivery_type_id',
            'region_id',
            'district',
            'city',
            'street',
            'building_number',
            'apartment_number',
            'floor_number',
            'np_city',
            'np_department',
            'sat_city',
            'sat_department',
            'recipient_type_id',
            'custom_first_name',
            'custom_last_name',
            'custom_phone',
            'custom_email',
            'payment_type_id',
            'payment_period',
            'mono_payment_period',
            'comment',
            'agreement',
        ]);
        $draft = array_filter($draft, fn ($value) => is_scalar($value) || is_null($value));

        $request->session()->flashInput($draft);
    }
}
