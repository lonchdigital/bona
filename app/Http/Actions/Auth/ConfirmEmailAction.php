<?php

namespace App\Http\Actions\Auth;

use App\Http\Requests\Auth\ConfirmEmailRequest;
use App\Services\Auth\AuthService;

class ConfirmEmailAction
{
    public function __invoke(ConfirmEmailRequest $request, AuthService $service)
    {
        if($service->confirmEmail($request->toDTO())) {
            return view('pages.auth.email-confirmation-success');
        } else {
            return view('pages.auth.email-confirmation-fail');
        }
    }
}
