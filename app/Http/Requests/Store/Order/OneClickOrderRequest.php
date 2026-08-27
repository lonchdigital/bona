<?php

namespace App\Http\Requests\Store\Order;

use App\Http\Requests\BaseRequest;
use App\Services\Order\DTO\OneClickOrderDTO;

class OneClickOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // The field carries an input mask, so an unfinished number still
            // holds its placeholders — refusing underscores is what catches it.
            'phone' => [
                'required',
                'string',
                'regex:/^[^_]*$/',
                'min:16',
            ],
            'agree' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => mb_strtolower(trans('base.name')),
            'phone' => mb_strtolower(trans('base.phone')),
            'agree' => mb_strtolower(trans('base.agree')),
        ];
    }

    public function toDTO(): OneClickOrderDTO
    {
        return new OneClickOrderDTO(
            name: $this->input('name'),
            phone: $this->input('phone'),
        );
    }
}
