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
            return redirect()->route('store.home');
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
