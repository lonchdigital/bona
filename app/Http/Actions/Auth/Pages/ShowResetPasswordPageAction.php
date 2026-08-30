<?php

namespace App\Http\Actions\Auth\Pages;

use Illuminate\Http\Request;

class ShowResetPasswordPageAction
{
    public function __invoke(Request $request, string $token)
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }
}
