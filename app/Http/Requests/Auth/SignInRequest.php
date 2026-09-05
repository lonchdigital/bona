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
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember_me' => [
                'nullable',
            ],
            'redirect_to' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'checkout_draft' => [
                'nullable',
                'string',
                'max:20000',
            ],
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
