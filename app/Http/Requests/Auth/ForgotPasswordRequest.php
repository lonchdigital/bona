<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Services\Auth\DTO\ForgotPasswordDTO;

class ForgotPasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => trans('auth.email'),
        ];
    }

    public function toDTO(): ForgotPasswordDTO
    {
        return new ForgotPasswordDTO(
            $this->input('email'),
        );
    }
}
