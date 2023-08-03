<?php

namespace App\Http\Actions\UserProfile\Pages;

class ShowPasswordEditPageAction
{
    public function __invoke()
    {
        return view('pages.user-profile.edit-password');
    }
}
