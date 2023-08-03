<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Rules\PhoneNumberLengthRule;
use App\Services\Auth\DTO\SignUpDTO;

class SignUpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],
            'first_name' => [
                'required',
                'max:100',
            ],
            'last_name' => [
                'required',
                'max:100',
            ],
            'phone' => [
                'required',
                new PhoneNumberLengthRule(12),
            ],
            'password' => [
                'required',
                'min:6',
                'max:50',
                'confirmed',
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
            'email' => trans('auth.email'),
            'first_name' => trans('auth.first_name'),
            'last_name' => trans('auth.last_name'),
            'phone' => trans('auth.phone'),
            'password' => trans('auth.password'),
            'password_confirmation' => trans('auth.password_confirmation'),
        ];
    }

    public function toDTO(): SignUpDTO
    {
        return new SignUpDTO(
            $this->input('email'),
            $this->input('first_name'),
            $this->input('last_name'),
            $this->input('phone'),
            $this->input('password'),
        );
    }
}
