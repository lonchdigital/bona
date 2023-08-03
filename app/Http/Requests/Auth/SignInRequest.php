<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Services\Auth\DTO\SignInDTO;

class SignInRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
            ],
            'password' => [
                'required'
            ],
            'remember_me' => [
                'nullable',
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => trans('auth.email'),
            'password' => trans('auth.password'),
        ];
    }

    public function toDTO(): SignInDTO
    {
        return new SignInDTO(
            $this->input('email'),
            $this->input('password'),
            (bool) $this->input('remember_me'),
        );
    }
}
