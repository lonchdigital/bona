<?php

namespace App\Http\Requests\UserProfile;

use App\Http\Requests\BaseRequest;
use App\Services\UserProfile\DTO\PasswordEditDTO;

class PasswordEditRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
            ],
            'new_password' => [
                'required',
                'min:6',
                'max:50',
                'same:password_confirmation'
            ],
            'password_confirmation' => [
                'required',
                'min:6',
                'max:50',
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => trans('user-profile.current_password'),
            'new_password' => trans('user-profile.new_password'),
            'password_confirmation' => trans('auth.password_confirmation'),
        ];
    }

    public function toDTO(): PasswordEditDTO
    {
        return new PasswordEditDTO(
            $this->input( 'current_password'),
            $this->input( 'new_password'),
            $this->input( 'password_confirmation'),
        );
    }
}
