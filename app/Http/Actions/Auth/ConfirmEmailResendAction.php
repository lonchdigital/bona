<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\ConfirmEmailResendRequest;
use App\Services\Auth\AuthService;

class ConfirmEmailResendAction
{
    public function __invoke(ConfirmEmailResendRequest $request, AuthService $service)
    {
        $service->confirmEmailResend($request->toDTO());

        return view('pages.auth.confirm-email');
    }
}
