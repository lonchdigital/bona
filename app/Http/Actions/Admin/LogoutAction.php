<?php

namespace App\Http\Actions\Admin;

use App\Services\Auth\AuthService;

class LogoutAction
{
    public function __invoke(AuthService $service)
    {
        $service->logOut();
        return redirect()->route('store.home');
    }
}
