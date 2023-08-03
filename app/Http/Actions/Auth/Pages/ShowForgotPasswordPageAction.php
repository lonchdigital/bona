<?php

namespace App\Http\Actions\Auth\Pages;

class ShowForgotPasswordPageAction
{
    public function __invoke()
    {
        return view('pages.auth.forgot-password');
    }
}
