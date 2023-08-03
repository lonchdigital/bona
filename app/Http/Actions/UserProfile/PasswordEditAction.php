<?php

namespace App\Http\Actions\UserProfile;

use App\Services\UserProfile\UserProfileService;
use App\Http\Requests\UserProfile\PasswordEditRequest;

class PasswordEditAction
{
    public function __invoke(PasswordEditRequest $request, UserProfileService $service)
    {
        if (!$service->updateAuthUserPassword($request->toDTO())) {
            return redirect()->back()->withErrors(['current_password' => trans('user-profile.current_password_is_incorrect')]);
        }

        return redirect()->back()->with([
            'message' => trans('user-profile.user_password_updated_successfully'),
        ]);
    }
}
