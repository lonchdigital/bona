<?php

namespace App\Services\UserProfile;

use App\Models\User;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\UserProfile\DTO\PasswordEditDTO;
use App\Services\UserProfile\DTO\UserProfileUpdateDTO;

class UserProfileService extends BaseService
{
    public function getAuthUserData(): User
    {
        return Auth::user();
    }

    public function updateAuthUserData(UserProfileUpdateDTO $request): void
    {
        $user = Auth::user();

        $user->update([
//           'email' => $request->email,
           'phone' => $request->phone,
           'first_name' => $request->firstName,
           'last_name' => $request->lastName,
        ]);
    }

    /**
     * returns false in case if current password is incorrect
     */
    public function updateAuthUserPassword(PasswordEditDTO $request): bool
    {
        $user = Auth::user();

        if (!Hash::check($request->currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($request->newPassword),
        ]);

        return true;
    }
}
