<?php

namespace App\Http\Actions\Auth\Pages;

class ShowSignUpPageAction
{
    public function __invoke()
    {
        return view('pages.auth.sign-up');
    }
}
