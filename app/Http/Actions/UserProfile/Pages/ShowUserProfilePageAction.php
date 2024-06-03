<?php

namespace App\Http\Actions\UserProfile\Pages;

use App\Services\UserProfile\UserProfileService;

class ShowUserProfilePageAction
{
    public function __invoke(UserProfileService $service)
    {
        $user = $service->getAuthUserData();
        return view('pages.user-profile.profile', compact('user'));
    }
}
