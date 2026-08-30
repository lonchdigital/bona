<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthService;

class ForgotPasswordAction
{
    public function __invoke(ForgotPasswordRequest $request, AuthService $service)
    {
        $service->sendPasswordResetLink($request->toDTO());

        return view('pages.auth.forgot-password-email-sent');
    }
}
