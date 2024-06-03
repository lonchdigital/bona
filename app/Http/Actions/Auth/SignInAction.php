<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\SignInRequest;
use App\Services\Auth\AuthService;

class SignInAction
{
    public function __invoke(SignInRequest $request, AuthService $service)
    {
        $dto = $request->toDTO();

        $signInResult = $service->signIn($dto);

        if ($signInResult) {
            if( auth()->user()->isAdmin() ) {
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
