<?php

namespace App\Http\Actions\Auth\Pages;

class ShowConfirmationEmailResendPageAction
{
    public function __invoke()
    {
        return view('pages.auth.confirm-email-resend');
    }
}
