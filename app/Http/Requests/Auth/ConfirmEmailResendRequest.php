<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Services\Auth\DTO\ConfirmEmailResendDTO;

class ConfirmEmailResendRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => trans('auth.email'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => trans('auth.user_with_such_email_not_found'),
        ];
    }

    public function toDTO(): ConfirmEmailResendDTO
    {
        return new ConfirmEmailResendDTO(
            $this->input('email'),
        );
    }
}
