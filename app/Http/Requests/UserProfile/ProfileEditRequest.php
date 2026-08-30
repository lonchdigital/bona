<?php

namespace App\Http\Requests\UserProfile;

use App\Http\Requests\BaseRequest;
use App\Rules\PhoneNumberLengthRule;
use App\Services\UserProfile\DTO\UserProfileUpdateDTO;
use Illuminate\Support\Facades\Auth;

class ProfileEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $user = Auth::user();

        return [
            'first_name' => [
                'required',
                'max:100',
            ],
            'last_name' => [
                'required',
                'max:100',
            ],
            /*'email' => [
                'required',
                'email' => 'unique:users,email,' . $user->id . ',id',
            ],*/
            'phone' => [
                'required',
                new PhoneNumberLengthRule(12),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            //            'email' => trans('auth.email'),
            'first_name' => trans('auth.first_name'),
            'last_name' => trans('auth.last_name'),
            'phone' => trans('auth.phone'),
        ];
    }

    public function toDTO(): UserProfileUpdateDTO
    {
        return new UserProfileUpdateDTO(
            $this->input('first_name'),
            $this->input('last_name'),
            //            $this->input('email'),
            $this->input('phone'),
        );
    }
}
