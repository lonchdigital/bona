<?php

namespace App\Http\Actions\UserProfile\Pages;

use App\Services\UserProfile\UserProfileService;

class ShowProfileEditPageAction
{
    public function __invoke(UserProfileService $service)
    {
        $user = $service->getAuthUserData();
        return view('pages.user-profile.edit', compact('user'));
    }
}
