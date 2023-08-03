<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\SignUpRequest;
use App\Services\Auth\AuthService;

class SignUpAction
{
    public function __invoke(SignUpRequest $request, AuthService $service)
    {
        $service->signUp($request->toDTO());

        return view('pages.auth.confirm-email');
    }
}
