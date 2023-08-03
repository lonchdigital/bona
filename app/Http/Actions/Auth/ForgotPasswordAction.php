<?php

namespace App\Http\Actions\Auth;

use App\Services\Auth\AuthService;
use App\Http\Requests\Auth\ForgotPasswordRequest;

class ForgotPasswordAction
{
    public function __invoke(ForgotPasswordRequest $request, AuthService $service)
    {
        $service->resetPassword($request->toDTO());

        return view('pages.auth.forgot-password-email-sent');
    }
}
