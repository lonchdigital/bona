<?php

namespace App\Http\Requests\Store\Email;

use App\Http\Requests\BaseRequest;
use App\Services\EmailService\DTO\UserChooseDoorsDTO;

class UserChooseDoorsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'phone' => [
                'required',
                'string',
                'digits:10',
            ],
            'agree' => [
                'accepted',
            ],
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => mb_strtolower(trans('base.name')),
            'phone' => mb_strtolower(trans('base.phone')),
            'agree' => mb_strtolower(trans('base.agree')),
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'phone.digits' => trans('base.phone_validation'),
        ];
    }

    public function toDTO(): UserChooseDoorsDTO
    {
        return new UserChooseDoorsDTO(
            $this->input('name'),
            $this->input('phone'),
        );
    }
}
