<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Services\Auth\DTO\ConfirmEmailDTO;

class ConfirmEmailRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'code' => [
                'nullable',
                'string',
            ]
        ];
    }


    public function toDTO(): ConfirmEmailDTO
    {
        return new ConfirmEmailDTO(
            $this->input('code')
        );
    }
}
