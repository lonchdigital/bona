<?php

namespace App\Http\Actions\UserProfile;

use App\Http\Requests\UserProfile\ProfileEditRequest;
use App\Services\UserProfile\UserProfileService;

class ProfileEditAction
{
    public function __invoke(ProfileEditRequest $request, UserProfileService $service)
    {
        $service->updateAuthUserData($request->toDTO());

        return redirect()->back()->with([
            'message' => trans('user-profile.user_data_updated_successfully'),
        ]);
    }
}
