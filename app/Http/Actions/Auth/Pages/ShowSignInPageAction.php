<?php

namespace App\Http\Actions\Auth\Pages;

class ShowSignInPageAction
{
    public function __invoke()
    {
        return view('pages.auth.sign-in');
    }
}
